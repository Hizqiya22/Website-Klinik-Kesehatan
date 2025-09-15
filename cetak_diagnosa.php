<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if (!in_array($_SESSION['role'], ['dokter', 'admin'])) {
    die("Unauthorized");
}

require('fpdf/fpdf.php');

if (!isset($_GET['jadwal_id'])) {
    die("ID tidak ditemukan.");
}

$jadwal_id = $_GET['jadwal_id'];

$stmt = $conn->prepare("SELECT
    p.nama AS nama_pasien,
    p.tanggal_lahir,
    p.jenis_kelamin,
    p.no_hp,
    p.alamat,
    d.nama AS nama_dokter,
    d.spesialis,
    jk.tanggal_kontrol,
    jk.jam_kontrol,
    dg.diagnosa,
    dg.resep,
    dg.catatan,
    dg.tanggal_diagnosa
FROM jadwal_kontrol jk
JOIN pasien p ON jk.pasien_id = p.id
JOIN dokter d ON jk.dokter_id = d.id
JOIN diagnosa dg ON jk.id = dg.jadwal_id
WHERE jk.id = ?");
$stmt->bind_param("i", $jadwal_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan.");
}

class PDF extends FPDF {
    function Header() {
        // Logo klinik
        $this->Image('logo-klinik.png',10,4,40);
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Klinik Uncip - Hasil Diagnosa Pasien',0,1,'C');
        $this->SetFont('Arial','I',10);
        $this->Cell(0,6,'Jl. Sehat Selalu No.88, Bandung | Telp: (022) 1234567',0,1,'C');
        $this->Ln(5);
        $this->SetDrawColor(0,0,0);
        $this->Line(10, 30, 200, 30);
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial','I',9);
        $this->Cell(0,10,'Dicetak pada: ' . date('d-m-Y H:i'),0,0,'L');
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'R');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',11);

$pdf->SetFillColor(230, 230, 255);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Informasi Pasien',0,1,'L', true);
$pdf->SetFont('Arial','',11);
$pdf->Ln(1);
$pdf->Cell(50,8,'Nama',0); $pdf->Cell(0,8,": {$data['nama_pasien']}",0,1);
$pdf->Cell(50,8,'Tanggal Lahir',0); $pdf->Cell(0,8,": {$data['tanggal_lahir']}",0,1);
$pdf->Cell(50,8,'Jenis Kelamin',0); $pdf->Cell(0,8,": {$data['jenis_kelamin']}",0,1);
$pdf->Cell(50,8,'No HP',0); $pdf->Cell(0,8,": {$data['no_hp']}",0,1);
$pdf->Cell(50,8,'Alamat',0); $pdf->MultiCell(0,8,": {$data['alamat']}",0);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(220, 255, 220);
$pdf->Cell(0,10,'Informasi Pemeriksaan',0,1,'L', true);
$pdf->SetFont('Arial','',11);
$pdf->Ln(1);
$pdf->Cell(50,8,'Dokter',0); $pdf->Cell(0,8,": dr. {$data['nama_dokter']} ({$data['spesialis']})",0,1);
$pdf->Cell(50,8,'Tanggal Kontrol',0); $pdf->Cell(0,8,": {$data['tanggal_kontrol']}",0,1);
$pdf->Cell(50,8,'Jam Kontrol',0); $pdf->Cell(0,8,": {$data['jam_kontrol']}",0,1);
$pdf->Cell(50,8,'Diagnosa Tanggal',0); $pdf->Cell(0,8,": {$data['tanggal_diagnosa']}",0,1);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(0,10,'Diagnosa',0,1,'L', true);
$pdf->SetFont('Arial','',11);
$pdf->MultiCell(0,8,$data['diagnosa'],1);

$pdf->Ln(3);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Resep Obat',0,1,'L', true);
$pdf->SetFont('Arial','',11);
$pdf->MultiCell(0,8,$data['resep'],1);

$pdf->Ln(3);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Catatan Tambahan',0,1,'L', true);
$pdf->SetFont('Arial','',11);
$pdf->MultiCell(0,8,$data['catatan'],1);

$pdf->Ln(20);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,8,"Hormat Kami,",0,1,'R');
$pdf->Ln(15);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,"dr. {$data['nama_dokter']}",0,1,'R');

$pdf->Output();
