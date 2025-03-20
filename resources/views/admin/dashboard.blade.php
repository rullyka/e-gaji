@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="text-white mb-0"><i class="fas fa-chart-line mr-2"></i>Selamat Datang di Sistem E-Gaji
                            </h2>
                            <p class="text-white lead mt-3 mb-0">
                                Kelola penggajian karyawan dengan mudah, cepat, dan akurat.
                                <br>Silahkan gunakan menu di sidebar untuk navigasi.
                            </p>
                        </div>
                        <div class="col-md-4 text-center d-none d-md-block">
                            <i class="fas fa-money-bill-wave fa-5x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Karyawan</h3>
                    <p>Kelola data karyawan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('karyawans.index') }}" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Penggajian</h3>
                    <p>Proses penggajian bulanan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Laporan</h3>
                    <p>Lihat dan cetak laporan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .small-box {
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .small-box .icon {
            opacity: 0.8;
        }

        .small-box:hover .icon {
            opacity: 1;
        }

        /* Animation for the money icon */
        .fa-money-bill-wave {
            animation: float 3s ease-in-out infinite;
        }

        /* Pulse animation for boxes */
        .small-box .icon i {
            animation: pulse 2s infinite;
        }

        /* Gradient animation for header card */
        .bg-gradient-primary {
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
        }

        /* Keyframes definitions */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Hi!');
    </script>
@stop
