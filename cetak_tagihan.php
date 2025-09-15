<?php
require('fpdf/fpdf.php');
include 'koneksi.php';

if (!isset($_GET['id'])) {
    die('ID tagihan tidak ditemukan.');
}

$id = $_GET['id'];

// Ambil data tagihan
$stmt = $conn->prepare("
    SELECT t.*, p.nama AS nama_pasien, d.nama AS nama_dokter, jk.tanggal_kontrol, jk.jam_kontrol
    FROM tagihan t
    JOIN jadwal_kontrol jk ON t.jadwal_id = jk.id
    JOIN pasien p ON jk.pasien_id = p.id
    JOIN dokter d ON jk.dokter_id = d.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die('Data tidak ditemukan.');
}

// Buat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

// Header Klinik
$pdf->SetFillColor(13, 71, 161); // Biru
$pdf->SetTextColor(255);
$pdf->Cell(190, 15, 'KLINIK UNCIP', 0, 1, 'C', true);

$pdf->Ln(5);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',14);
$pdf->Cell(190, 10, 'BUKTI PEMBAYARAN', 0, 1, 'C');

$pdf->Ln(5);

// Informasi Umum
$pdf->SetFont('Arial','',12);
$pdf->Cell(50, 8, 'Nama Pasien', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(100, 8, $data['nama_pasien'], 0, 1);

$pdf->Cell(50, 8, 'Nama Dokter', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(100, 8, $data['nama_dokter'], 0, 1);

$pdf->Cell(50, 8, 'Tanggal Kontrol', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(100, 8, $data['tanggal_kontrol'] . ' ' . $data['jam_kontrol'], 0, 1);

$pdf->Cell(50, 8, 'Metode Pembayaran', 0, 0);
$pdf->Cell(5, 8, ':', 0, 0);
$pdf->Cell(100, 8, ucfirst($data['metode_pembayaran']), 0, 1);

$pdf->Ln(5);

// Rincian Biaya
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190, 8, 'Rincian Tagihan', 0, 1);
$pdf->SetFont('Arial','',12);

$pdf->Cell(100, 8, 'Biaya Kontrol', 0, 0);
$pdf->Cell(90, 8, 'Rp ' . number_format($data['biaya_kontrol']), 0, 1);

$pdf->Cell(100, 8, 'Biaya Administrasi', 0, 0);
$pdf->Cell(90, 8, 'Rp ' . number_format($data['biaya_administrasi']), 0, 1);

$pdf->Cell(100, 8, 'Biaya Tambahan', 0, 0);
$pdf->Cell(90, 8, 'Rp ' . number_format($data['biaya_tambahan']), 0, 1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100, 10, 'Total Pembayaran', 0, 0);
$pdf->Cell(90, 10, 'Rp ' . number_format($data['total']), 0, 1);

$pdf->Ln(10);

// Status
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0, 128, 0);
$pdf->Cell(190, 10, 'Status: SUDAH DIBAYAR', 0, 1, 'C');
$pdf->SetTextColor(0);

$pdf->Ln(20);
$pdf->SetFont('Arial','',11);
$pdf->Cell(190, 6, 'Dicetak pada: ' . date('d-m-Y H:i'), 0, 1, 'R');

$pdf->Output('I', 'Bukti_Pembayaran.pdf');
?>
