<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Prediksi Kelulusan Mahasiswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card shadow-lg border-0">

                <div class="card-header bg-primary text-white text-center py-4">

                    <h2 class="mb-2">
                        Sistem Prediksi Kelulusan Mahasiswa
                    </h2>

                    <p class="mb-0">
                        Implementasi Perbandingan 3 Algoritma Klasifikasi
                    </p>

                </div>

                <div class="card-body">

                    <!-- Informasi Sistem -->

                    <div class="row mb-4">

                        <div class="col-md-6">

                            <div class="card border-success">
                                <div class="card-body text-center">

                                    <h6 class="text-success">
                                        Data Training
                                    </h6>

                                    <h3>
                                        {{ $totalTraining ?? 500 }}
                                    </h3>

                                    <small>
                                        Data Historis Mahasiswa
                                    </small>

                                </div>
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="card border-info">
                                <div class="card-body text-center">

                                    <h6 class="text-info">
                                        Model Klasifikasi
                                    </h6>

                                    <h3>
                                        3 Algoritma
                                    </h3>

                                    <small>
                                        Naive Bayes, KNN, Decision Tree
                                    </small>

                                </div>
                            </div>

                        </div>

                    </div>

                    <hr>

                    <h4 class="mb-3">
                        Data Testing
                    </h4>

                    <form action="{{ url('/predict') }}" method="POST">

                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold text-primary">
                                    Metode Algoritma Klasifikasi
                                </label>
                                <select class="form-select border-primary" name="algoritma" required>
                                    <option value="naive_bayes" {{ session('algoritma', 'naive_bayes') == 'naive_bayes' ? 'selected' : '' }}>Naive Bayes</option>
                                    <option value="knn" {{ session('algoritma') == 'knn' ? 'selected' : '' }}>K-Nearest Neighbors (KNN)</option>
                                    <option value="decision_tree" {{ session('algoritma') == 'decision_tree' ? 'selected' : '' }}>Decision Tree</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    IPK
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="4"
                                    name="ipk"
                                    class="form-control"
                                    placeholder="Contoh: 3.50"
                                    required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    Kehadiran (%)
                                </label>

                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    name="kehadiran"
                                    class="form-control"
                                    placeholder="Contoh: 90"
                                    required>

                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    SKS Lulus
                                </label>

                                <input
                                    type="number"
                                    name="sks_lulus"
                                    class="form-control"
                                    placeholder="Contoh: 120"
                                    required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-bold">
                                    Status Kerja
                                </label>

                                <select
                                    class="form-select"
                                    name="status_kerja"
                                    required>

                                    <option value="">
                                        -- Pilih Status --
                                    </option>

                                    <option value="Ya">
                                        Ya
                                    </option>

                                    <option value="Tidak">
                                        Tidak
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="d-grid mt-4">

                            <button
                                type="submit"
                                class="btn btn-success btn-lg">

                                Prediksi Kelulusan

                            </button>

                        </div>

                    </form>

                </div>

                <div class="card-footer text-center text-muted">

                    Data Mining - Classification Algorithms Comparison

                </div>

            </div>

        </div>

    </div>

</div>

@if(session('prediction'))

<script>

document.addEventListener('DOMContentLoaded', function() {

    Swal.fire({

        icon: '{{ session("prediction") == "Ya" ? "success" : "warning" }}',

        title: 'Hasil Prediksi',

        html: `
            <div style="text-align:left;font-size:15px;">

                <p>
                    <strong>Metode :</strong> 
                    {{ session('algoritma') == 'knn' ? 'K-Nearest Neighbors (K=5)' : (session('algoritma') == 'decision_tree' ? 'Decision Tree' : 'Naive Bayes') }}
                </p>

                <p>
                    <strong>Status Kelulusan :</strong>
                </p>

                <h4 style="color:
                {{ session('prediction') == 'Ya'
                    ? '#198754'
                    : '#dc3545' }}">
                    {{ session('prediction') == 'Ya'
                        ? '✅ Lulus Tepat Waktu'
                        : '❌ Tidak Lulus Tepat Waktu' }}
                </h4>

                <hr>

                @if(session('algoritma') == 'naive_bayes')
                <p>
                    Probabilitas Ya :
                    <strong>
                        {{ round(session('prob_ya',0) * 100,2) }}%
                    </strong>
                </p>

                <p>
                    Probabilitas Tidak :
                    <strong>
                        {{ round(session('prob_tidak',0) * 100,2) }}%
                    </strong>
                </p>
                @elseif(session('algoritma') == 'knn')
                <p>
                    Berdasarkan 5 data mahasiswa dengan nilai paling mirip (K=5):
                </p>
                <ul style="list-style-type: none; padding-left: 0; margin-bottom: 0;">
                    <li style="margin-bottom: 5px;">✅ Mirip dengan yang <strong>Lulus</strong> : <strong>{{ session('vote_ya') }} orang</strong></li>
                    <li>❌ Mirip dengan yang <strong>Tidak Lulus</strong> : <strong>{{ session('vote_tidak') }} orang</strong></li>
                </ul>
                @elseif(session('algoritma') == 'decision_tree')
                <p>
                    Rute Keputusan (Rules) :
                </p>
                <div style="background-color:#f8f9fa; padding:10px; border-left: 4px solid #0d6efd; font-family:monospace; font-size:13px; line-height:1.5;">
                    @foreach(session('path', []) as $step)
                        ➔ {{ $step }} <br>
                    @endforeach
                    <strong style="color: {{ session('prediction') == 'Ya' ? '#198754' : '#dc3545' }}">➔ Prediksi: {{ session('prediction') }}</strong>
                </div>
                @endif

            </div>
        `,

        width: 650,
        confirmButtonText: 'Tutup'

    });

});

</script>

@endif

</body>

</html>