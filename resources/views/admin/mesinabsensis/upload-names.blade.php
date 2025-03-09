@extends('adminlte::page')

@section('title', 'Upload Nama Karyawan ke Mesin Absensi')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Upload Nama Karyawan ke Mesin Absensi</h1>
    <div>
        <a href="{{ route('mesinabsensis.show', $mesinabsensi) }}" class="mr-1 btn btn-info">
            <i class="fas fa-info-circle"></i> Detail Mesin
        </a>
        <a href="{{ route('mesinabsensis.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upload Nama Single</h3>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                <form action="{{ route('mesinabsensis.upload-names-store', $mesinabsensi) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="user_id">NIK Karyawan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" value="{{ old('user_id') }}" required>
                        <small class="form-text text-muted">Masukkan NIK (numerik) yang dikenali oleh mesin absensi</small>
                        @error('user_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="name">Nama Karyawan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        <small class="form-text text-muted">Masukkan nama yang akan ditampilkan di mesin absensi (maks. 24 karakter)</small>
                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Nama
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(session('upload_results'))
        <div class="mt-4 card">
            <div class="card-header bg-info">
                <h3 class="card-title">Hasil Upload</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('upload_results') as $index => $result)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $result }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Upload dari Database Karyawan</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="search_karyawan">Cari Karyawan</label>
                    <input type="text" class="form-control" id="search_karyawan" placeholder="Masukkan nama karyawan atau NIK">
                    <small class="text-muted">Ketik minimal 3 karakter untuk mencari</small>
                </div>

                <div class="mt-3 table-responsive" id="search_results" style="display: none;">
                    <table class="table table-bordered table-hover" id="karyawanSearchTable">
                        <thead>
                            <tr>
                                <th width="60">Pilih</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Will be filled by AJAX -->
                        </tbody>
                    </table>
                </div>

                <hr>

                <form action="{{ route('mesinabsensis.upload-names-batch', $mesinabsensi) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Antrian Upload</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="queueTable">
                                    <tr id="noData">
                                        <td colspan="3" class="text-center">Belum ada data dalam antrian</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="btnUploadBatch" disabled>
                            <i class="fas fa-upload"></i> Upload Batch
                        </button>
                        <button type="button" class="btn btn-danger" id="btnClearQueue">
                            <i class="fas fa-trash"></i> Bersihkan Antrian
                        </button>
                    </div>

                    <!-- Hidden input for batch upload data -->
                    <input type="hidden" name="batch_data" id="batchData" value="">
                </form>
            </div>
        </div>

        <div class="mt-4 card">
            <div class="card-header bg-warning">
                <h3 class="card-title">Petunjuk</h3>
            </div>
            <div class="card-body">
                <h5>Format Penamaan di Mesin Absensi:</h5>
                <ol>
                    <li>NIK karyawan harus berupa angka.</li>
                    <li>Nama karyawan sebaiknya tidak lebih dari 24 karakter.</li>
                    <li>Hindari penggunaan karakter khusus dalam nama.</li>
                </ol>

                <div class="mt-3 alert alert-info">
                    <h5><i class="icon fas fa-info-circle"></i> Catatan Penting!</h5>
                    <p class="mb-0">Upload nama karyawan tidak akan menghapus data yang sudah ada di mesin absensi. Jika NIK sudah terdaftar, nama yang baru akan menimpa nama yang lama.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function() {
        // Queue for batch upload
        let queue = [];

        // Handle search for employees
        let searchTimeout;
        $('#search_karyawan').on('keyup', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();

            if (query.length < 3) {
                $('#search_results').hide();
                return;
            }

            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: '{{ route("karyawans.search") }}'
                    , method: 'GET'
                    , data: {
                        q: query
                    }
                    , dataType: 'json'
                    , success: function(response) {
                        let html = '';

                        if (response.data.length === 0) {
                            html = '<tr><td colspan="4" class="text-center">Tidak ada data karyawan yang sesuai</td></tr>';
                        } else {
                            response.data.forEach(function(karyawan) {
                                html += `
                                    <tr>
                                        <td class="text-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="check_${karyawan.id}">
                                            </div>
                                        </td>
                                        <td>${karyawan.nik ?? 'N/A'}</td>
                                        <td>${karyawan.nama_karyawan}</td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm btn-add-to-queue"
                                                data-id="${karyawan.nik}"
                                                data-name="${karyawan.nama_karyawan}">
                                                <i class="fas fa-plus"></i> Tambah
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        $('#karyawanSearchTable tbody').html(html);
                        $('#search_results').show();

                        // Handle add to queue
                        $('.btn-add-to-queue').on('click', function() {
                            const id = $(this).data('id');
                            const name = $(this).data('name');
                            addToQueue(id, name);
                        });
                    }
                    , error: function(xhr) {
                        console.error('Error searching for employees:', xhr);
                    }
                });
            }, 500);
        });

        // Add to queue
        function addToQueue(id, name) {
            // Validate ID is numeric
            if (!id || isNaN(id)) {
                alert('NIK karyawan harus berupa angka!');
                return;
            }

            // Check if already in queue
            if (queue.some(item => item.id === id)) {
                alert('Karyawan ini sudah ada dalam antrian!');
                return;
            }

            // Add to queue
            queue.push({
                id: id
                , name: name
            });

            updateQueueTable();
        }

        // Remove from queue
        $(document).on('click', '.btn-remove-queue', function() {
            const id = $(this).data('id');
            queue = queue.filter(item => item.id !== id);
            updateQueueTable();
        });

        // Clear queue
        $('#btnClearQueue').on('click', function() {
            if (confirm('Anda yakin ingin mengosongkan antrian?')) {
                queue = [];
                updateQueueTable();
            }
        });

        // Update queue table
        function updateQueueTable() {
            const tableBody = $('#queueTable');
            tableBody.empty();

            if (queue.length === 0) {
                tableBody.append('<tr id="noData"><td colspan="3" class="text-center">Belum ada data dalam antrian</td></tr>');
                $('#btnUploadBatch').prop('disabled', true);
                $('#batchData').val('');
                return;
            }

            $('#btnUploadBatch').prop('disabled', false);

            queue.forEach(item => {
                tableBody.append(`
                    <tr>
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm btn-remove-queue" data-id="${item.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Update hidden input
            $('#batchData').val(JSON.stringify(queue));
        }
    });

</script>
@stop
