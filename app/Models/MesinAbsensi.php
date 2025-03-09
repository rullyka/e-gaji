<?php

namespace App\Models;

use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MesinAbsensi extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mesinabsensis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'alamat_ip',
        'kunci_komunikasi',
        'lokasi',
        'status_aktif',
        'keterangan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    /**
     * Get the absensis that use this machine for check-in.
     */
    public function absensisMasuk()
    {
        return $this->hasMany(Absensi::class, 'mesinabsensi_masuk_id');
    }

    /**
     * Get the absensis that use this machine for check-out.
     */
    public function absensisPulang()
    {
        return $this->hasMany(Absensi::class, 'mesinabsensi_pulang_id');
    }

    /**
     * Get all karyawan registered in this machine
     * This is done through the karyawan_mesinabsensi pivot table
     */
    public function karyawans()
    {
        return $this->belongsToMany(Karyawan::class, 'karyawan_mesinabsensi', 'mesinabsensi_id', 'karyawan_id')
                    ->withPivot('status_sync', 'sync_at')
                    ->withTimestamps();
    }

    /**
     * Get all registered karyawan that are synced successfully
     */
    public function syncedKaryawans()
    {
        return $this->karyawans()->wherePivot('status_sync', true);
    }

    /**
     * Get all registered karyawan that failed to sync
     */
    public function failedSyncKaryawans()
    {
        return $this->karyawans()->wherePivot('status_sync', false);
    }

    /**
     * Test connection to the machine
     *
     * @return bool|string True if connected, error message otherwise
     */
    public function testConnection()
    {
        $connect = @fsockopen($this->alamat_ip, "80", $errno, $errstr, 1);
        if ($connect) {
            fclose($connect);
            return true;
        }
        return "Error: " . $errstr . " (" . $errno . ")";
    }

    /**
     * Get attendance logs from the machine
     *
     * @return array|string Array of logs if successful, error message otherwise
     */
    public function getAttendanceLogs()
    {
        $connect = @fsockopen($this->alamat_ip, "80", $errno, $errstr, 1);
        if ($connect) {
            $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">" . $this->kunci_komunikasi . "</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";
            $newLine = "\r\n";

            fputs($connect, "POST /iWsService HTTP/1.0" . $newLine);
            fputs($connect, "Content-Type: text/xml" . $newLine);
            fputs($connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
            fputs($connect, $soap_request . $newLine);

            $buffer = "";
            while ($response = fgets($connect, 1024)) {
                $buffer = $buffer . $response;
            }
            fclose($connect);

            return $this->parseAttendanceLogs($buffer);
        }

        return "Error: Failed to connect to " . $this->alamat_ip;
    }

    /**
     * Upload user name to the machine
     *
     * @param string $userId User ID
     * @param string $name User name
     * @return string Result message
     */
    public function uploadName($userId, $name)
    {
        $connect = @fsockopen($this->alamat_ip, "80", $errno, $errstr, 1);
        if ($connect) {
            $soap_request = "<SetUserInfo><ArgComKey Xsi:type=\"xsd:integer\">" . $this->kunci_komunikasi . "</ArgComKey><Arg><PIN>" . $userId . "</PIN><Name>" . $name . "</Name></Arg></SetUserInfo>";
            $newLine = "\r\n";

            fputs($connect, "POST /iWsService HTTP/1.0" . $newLine);
            fputs($connect, "Content-Type: text/xml" . $newLine);
            fputs($connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
            fputs($connect, $soap_request . $newLine);

            $buffer = "";
            while ($response = fgets($connect, 1024)) {
                $buffer = $buffer . $response;
            }
            fclose($connect);

            $result = $this->parseDataBetween($buffer, "<Information>", "</Information>");

            // Update registration if user exists in database
            $karyawan = Karyawan::where('nik', $userId)->first();
            if ($karyawan) {
                $success = strpos($result, 'successful') !== false || strpos($result, 'berhasil') !== false;
                $karyawan->registerToMachine($this->id, $success);
            }

            return $result;
        }

        return "Error: Failed to connect to " . $this->alamat_ip;
    }

    /**
     * Delete user from the machine
     *
     * @param string $userId User ID
     * @return string Result message
     */
    public function deleteUser($userId)
    {
        $connect = @fsockopen($this->alamat_ip, "80", $errno, $errstr, 1);
        if ($connect) {
            $soap_request = "<DeleteUser><ArgComKey Xsi:type=\"xsd:integer\">" . $this->kunci_komunikasi . "</ArgComKey><Arg><PIN>" . $userId . "</PIN></Arg></DeleteUser>";
            $newLine = "\r\n";

            fputs($connect, "POST /iWsService HTTP/1.0" . $newLine);
            fputs($connect, "Content-Type: text/xml" . $newLine);
            fputs($connect, "Content-Length: " . strlen($soap_request) . $newLine . $newLine);
            fputs($connect, $soap_request . $newLine);

            $buffer = "";
            while ($response = fgets($connect, 1024)) {
                $buffer = $buffer . $response;
            }
            fclose($connect);

            $result = $this->parseDataBetween($buffer, "<Information>", "</Information>");

            // Update registration if user exists in database
            $karyawan = Karyawan::where('nik', $userId)->first();
            if ($karyawan) {
                $karyawan->unregisterFromMachine($this->id);
            }

            return $result;
        }

        return "Error: Failed to connect to " . $this->alamat_ip;
    }

    /**
     * Sync all active employees to the machine
     *
     * @return array Success and failure count, and detailed results
     */
    public function syncAllKaryawans()
    {
        $karyawans = Karyawan::whereNull('tahun_keluar')->get();
        $success = 0;
        $failed = 0;
        $results = [];

        foreach ($karyawans as $karyawan) {
            if (!$karyawan->nik) {
                $results[] = "Karyawan {$karyawan->nama_karyawan}: Tidak memiliki NIK";
                $failed++;
                continue;
            }

            $result = $this->uploadName($karyawan->nik, $karyawan->nama_karyawan);

            if (strpos($result, 'successful') !== false || strpos($result, 'berhasil') !== false) {
                $success++;
            } else {
                $failed++;
            }

            $results[] = "Karyawan {$karyawan->nama_karyawan} (NIK: {$karyawan->nik}): {$result}";
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Parse attendance logs from buffer
     *
     * @param string $buffer Response buffer
     * @return array Array of attendance logs
     */
    private function parseAttendanceLogs($buffer)
    {
        $logs = [];
        $data = $this->parseDataBetween($buffer, "<GetAttLogResponse>", "</GetAttLogResponse>");
        $rows = explode("\r\n", $data);

        foreach ($rows as $row) {
            if (!empty($row)) {
                $rowData = $this->parseDataBetween($row, "<Row>", "</Row>");
                if (!empty($rowData)) {
                    $logs[] = [
                        'pin' => $this->parseDataBetween($rowData, "<PIN>", "</PIN>"),
                        'datetime' => $this->parseDataBetween($rowData, "<DateTime>", "</DateTime>"),
                        'verified' => $this->parseDataBetween($rowData, "<Verified>", "</Verified>"),
                        'status' => $this->parseDataBetween($rowData, "<Status>", "</Status>")
                    ];
                }
            }
        }

        return $logs;
    }

    /**
     * Parse data between two strings
     *
     * @param string $data Original data
     * @param string $start Start delimiter
     * @param string $end End delimiter
     * @return string Parsed data
     */
    private function parseDataBetween($data, $start, $end)
    {
        $data = " " . $data;
        $startPos = strpos($data, $start);
        if ($startPos == 0) return "";

        $startPos += strlen($start);
        $endPos = strpos($data, $end, $startPos);
        if ($endPos == 0) return "";

        return substr($data, $startPos, $endPos - $startPos);
    }
}