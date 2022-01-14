<?php
function pd($value)
{
	echo "<pre>";
	print_r($value);
	die;
}

// generate_kode_surat 
function gen_no_surat($m_kode_id)
{
	// format no_urut(pertahun)/MN.bln.thn/kode_surat
	// format xxx/MN.xx.xx/xxxx
    $config = config('DB');
    $db = new Cahkampung\Landadb($config['db']);

    // ambil kode surat
    $kode_surat = $db->find("SELECT * FROM m_kode WHERE id ='{$m_kode_id}'");
    $kode_surat = $kode_surat->kode;

    // ambil surat terakhir di tahun ini
    $surat_terakhir = $db->select("*")
    	->from('t_surat_keluar')
    	->where("YEAR(tgl_surat)", '=', date('Y'))
    	->customWhere('no_surat IS NOT NULL', 'AND')
    	->orderBy("no_surat DESC")
    	->find();

	$bulan   = date('m');
	$tahun   = date('y');
	$no_urut = 1;
	if (!empty($surat_terakhir)) {
		$no_surat = $surat_terakhir->no_surat;
		$no_urut  = (int) substr($no_surat, 0, 3);
		$no_urut += 1;
		$no_urut  = str_pad($no_urut,3,"0", STR_PAD_LEFT);
	} else {
		$no_urut  = str_pad($no_urut,3,"0", STR_PAD_LEFT);
	}
	$no_surat_baru =  $no_urut . '/MN.' . $bulan . '.' . $tahun . '/' . $kode_surat; 

	return $no_surat_baru;
}