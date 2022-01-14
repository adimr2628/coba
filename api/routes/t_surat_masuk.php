<?php

/**
 * Validasi
 * @param  array $data
 * @param  array $custom
 * @return array
 */
function validasi($data, $custom = array())
{
    $validasi = array(
        "no_surat"     => "required",
        "tgl_surat"    => "required",
        "tgl_diterima" => "required",
        "dari"         => "required",
    );
    $cek = validate($data, $validasi, $custom);
    return $cek;
}

/**
 * Ambil semua t surat masuk
 */
$app->get("/t_surat_masuk/index", function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;
    $db->select("*")
        ->from("t_surat_masuk")
        ->orderBy("id DESC");

    /**
     * Filter
     */
    if (isset($params["filter"])) {
        $filter = (array) json_decode($params["filter"]);
        foreach ($filter as $key => $val) {
            $db->where($key, "LIKE", $val);
        }
    }
    /**
     * Set limit dan offset
     */
    if (isset($params["limit"]) && !empty($params["limit"])) {
        $db->limit($params["limit"]);
    }
    if (isset($params["offset"]) && !empty($params["offset"])) {
        $db->offset($params["offset"]);
    }

    $models    = $db->findAll();
    $totalItem = $db->count();

    // disposisi
    foreach ($models as $data) {
        $disposisi = $db->select("*")
            ->from('t_disposisi')
            ->where('t_surat_masuk_id', '=', $data->id)
            ->find();
        if ($disposisi) {
            // jika sudah pernah di disposisikan
            $disposisi->perintah = json_decode($disposisi->perintah); // ubah string jadi array

            // ambil penerima disposisi
            $penerima = $db->select('t_disposisi_user.m_user_id as id, m_user.nama, m_user.email, m_roles.nama as jabatan, t_disposisi_user.status')
                ->from('t_disposisi_user')
                ->join('left join', 'm_user', 't_disposisi_user.m_user_id = m_user.id')
                ->join('left join', 'm_roles', 'm_user.m_roles_id = m_roles.id')
                ->where('t_disposisi_id', '=', $disposisi->id)
                ->findAll();

            $disposisi->tujuan = $penerima;
        }
        $data->disposisi = $disposisi;
    }

    /**
     * GET KODE SURAT
     */
    $kode = $db->select('*')->from('m_kode')->findAll();

    /**
     * GET USERS
     */
    $users = $db->select('m_user.id, m_user.nama, m_user.email, m_roles.nama as jabatan')
        ->from('m_user')
        ->join('left join', 'm_roles', 'm_user.m_roles_id = m_roles.id')
        // ->where('m_roles_id', '=', 3)
        ->where('m_user.is_deleted', '=', '0')
        ->customWhere("m_user.m_roles_id IN (2,12)", "AND")
        ->findAll();

    return successResponse($response, ["list" => $models, "totalItems" => $totalItem, 'kode_surat' => $kode, 'users' => $users]);
});

/**
 * Save t surat masuk
 */
$app->post("/t_surat_masuk/save", function ($request, $response) {
    $param    = $request->getParams();
    $data     = $param['form'];
    $db       = $this->db;
    $validasi = validasi($data);
    if ($validasi === true) {
        try {
            if (isset($data["id"])) {
                $data['tgl_surat'] = date("Y-m-d", strtotime($data['tgl_surat']));
                $data['tgl_diterima'] = date("Y-m-d", strtotime($data['tgl_diterima']));
                $model = $db->update("t_surat_masuk", $data, ["id" => $data["id"]]);
            } else {
                $insert = [
                    'no_surat'     => $data['no_surat'],
                    'dari'         => $data['dari'],
                    'tgl_surat'    => date("Y-m-d", strtotime($data['tgl_surat'])),
                    'tgl_diterima' => date("Y-m-d", strtotime($data['tgl_diterima'])),
                    'perihal'      => $data['perihal'],
                    'm_kode_id'    => $data['m_kode_id'],
                    'm_user_id'    => $_SESSION['user']['id'],
                ];
                if (isset($_FILES['file'])) {

                    $target_dir    = "uploads/surat_masuk/";
                    $target_file   = $target_dir . date('YmdHis') . '_' . str_replace(' ', '', basename($_FILES["file"]["name"]));
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check file size | 5Mb
                    if ($_FILES["file"]["size"] > 5000000) {
                        return unprocessResponse($response, ["ukuran file terlalu besar"]);
                    }

                    // Allow certain file formats
                    if ($imageFileType != "pdf" && $imageFileType != "docx" && $imageFileType != "doc"
                        && $imageFileType != "png" && $imageFileType != "jpg" && $imageFileType != "jpeg") {
                        return unprocessResponse($response, ["ekstensi file yang anda upload tidak sesuai"]);
                    }

                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                        $insert['file'] = $target_file;
                    } else {
                        return unprocessResponse($response, ["gagal upload file"]);
                    }
                }
                $model = $db->insert("t_surat_masuk", $insert);
            }
            return successResponse($response, $model);
        } catch (Exception $e) {
            return unprocessResponse($response, [$e->getMessage()]);
        }
    }
    return unprocessResponse($response, $validasi);
});

/**
 * Hapus t surat masuk
 */
$app->post("/t_surat_masuk/hapus", function ($request, $response) {
    $data = $request->getParams();
    $db   = $this->db;
    try {
        $dataFile = $db->select('file')->from('t_surat_masuk')->where('id', '=', $data['id'])->find();
        if ($dataFile->file != null) {
            if (file_exists($dataFile->file)) {
                unlink($dataFile->file);
            }
        }
        $model = $db->delete("t_surat_masuk", ["id" => $data["id"]]);
        return successResponse($response, $model);
    } catch (Exception $e) {
        return unprocessResponse($response, $e);
    }
    return unprocessResponse($response, []);
});
$app->get("/t_surat_masuk/download", function ($request, $response) {
    $data = $request->getParams();
    $file_name = $data['file'];
    // var_dump($file_name);die;
    // if (file_exists($file_name)) {
    //     echo basename($file_name);
    // } else {
    //     echo "gak ada";
    // }
    // die;
    // pd($file_name);
    // pd($file_name);
    // $file_url = 'http://www.myremoteserver.com/' . $file_name;
    if ($fd = fopen ($file_name, "r")) {

        $fsize = filesize($file_name);
        $path_parts = pathinfo($file_name);
        $ext = strtolower($path_parts["extension"]);

        header("Content-type: application/pdf");
        header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\"");            
        header("Content-length: $fsize");
        header("Cache-control: private");

        while(!feof($fd)) {
            $buffer = fread($fd, 2048);
            echo $buffer;
        }
    }

    fclose ($fd);
    exit;
});

/**
 * Disposisi
 */
$app->post("/t_surat_masuk/disposisi", function ($request, $response) {
    $data = $request->getParams();
    $db   = $this->db;
    try {
        if (isset($data['id'])) {
            // update
            // update table t_disposisi
            $update = [
                "sifat"    => $data["sifat"],
                "perintah" => json_encode($data["perintah"]),
                "isi"      => $data["isi"],
            ];
            $db->update("t_disposisi", $update, ["id" => $data["id"]]);

            // update table t_disposisi_user
            $db->delete('t_disposisi_user', ["t_disposisi_id" => $data['id']]);
            foreach ($data['tujuan'] as $user) {
                $db->query("INSERT INTO t_disposisi_user(t_disposisi_id, m_user_id) VALUES($data[id], $user[id])");
            }
            return successResponse($response, "Data Berhasil diubah");
        } else {
            // create
            // insert into table t_disposisi
            $insert = [
                "sifat"            => $data["sifat"],
                "perintah"         => json_encode($data["perintah"]),
                "isi"              => $data["isi"],
                "t_surat_masuk_id" => $data['t_surat_masuk_id'],
            ];
            $model = $db->insert("t_disposisi", $insert);

            // insert into table t_disposisi_user
            $idSuratMasuk = $db->find("SELECT id from t_disposisi ORDER BY id DESC");
            foreach ($data['tujuan'] as $user) {
                $db->query("INSERT INTO t_disposisi_user(t_disposisi_id, m_user_id, status) VALUES($idSuratMasuk->id, " . $user['id'] . ", 0)");
            }
            return successResponse($response, "Data Berhasil disimpan");
        }
    } catch (Exception $e) {
        // echo "asu";die;
        return unprocessResponse($response, $e);
    }
});

$app->get('/t_surat_masuk/printDisposisi', function ($request, $response) {
    $param = $request->getParams();
    $db    = $this->db;

    // get data Disposisi
    $db->select('t_surat_masuk.*, t_disposisi.sifat, t_disposisi.perintah, t_disposisi.isi')
        ->from('t_disposisi')
        ->leftJoin('t_surat_masuk', 't_disposisi.t_surat_masuk_id = t_surat_masuk.id')
        ->where('t_disposisi.id', '=', $param['id']);
    $dataDisposisi = $db->find();

    // convert data perintah menjadi array
    $dataDisposisi->perintah = (array) json_decode($dataDisposisi->perintah);

    $db->select('m_user.*')
        ->from('t_disposisi_user')
        ->leftJoin('m_user', 't_disposisi_user.m_user_id = m_user.id')
        ->where('t_disposisi_user.t_disposisi_id', '=', $param['id']);
    $dataDisposisi->tujuan = $db->findAll();

    $listPerintah = [
        'Menghadap pimpinan',
        'Tanggapi',
        'Mengtahui / membaca',
        'Proses sesuai aturan',
        'Siapkan SPPD',
        'Koordinasi / konfirmasi',
        'Rapatkan',
        'Laporkan',
        'Arsip',
        'Saran / pertimbangan',
        'Telaahan staff / nota dinas',
        'Mewakili / mewakilkan',
        'Hadiri',
    ];

    $view = $this->view->fetch('print/disposisi.html', [
        'disposisi'    => $dataDisposisi,
        'listPerintah' => $listPerintah,
    ]);

    echo $view;
    die;

    return successResponse($response, $dataDisposisi);
});

/**
 * Melihat daftar surat disposisi
 */
$app->get('/t_surat_masuk/disposisi/index', function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;
    $db->select("t_surat_masuk.*, m_kode.nama, t_disposisi_user.status")
        ->from("t_surat_masuk")
        ->leftJoin("t_disposisi", 't_surat_masuk.id = t_disposisi.t_surat_masuk_id')
        ->leftJoin("t_disposisi_user", 't_disposisi.id = t_disposisi_user.t_disposisi_id')
        ->leftJoin("m_kode", "m_kode.id = t_surat_masuk.m_kode_id")
        ->where("t_disposisi_user.m_user_id", '=', $_SESSION['user']['id']);

    /**
     * Filter
     */
    if (isset($params["filter"])) {
        $filter = (array) json_decode($params["filter"]);
        foreach ($filter as $key => $val) {
            $db->where($key, "LIKE", $val);
        }
    }
    /**
     * Set limit dan offset
     */
    if (isset($params["limit"]) && !empty($params["limit"])) {
        $db->limit($params["limit"]);
    }
    if (isset($params["offset"]) && !empty($params["offset"])) {
        $db->offset($params["offset"]);
    }

    $db->orderBy("t_surat_masuk.tgl_diterima DESC");
    $models    = $db->findAll();
    $totalItem = $db->count();

    // disposisi
    foreach ($models as $data) {
        $disposisi = $db->select("*")
            ->from('t_disposisi')
            ->where('t_surat_masuk_id', '=', $data->id)
            ->find();
        if ($disposisi) {
            // jika sudah pernah di disposisikan
            $disposisi->perintah = json_decode($disposisi->perintah); // ubah string jadi array

            // ambil penerima disposisi
            $penerima = $db->select('t_disposisi_user.m_user_id as id, m_user.nama, m_user.email, m_roles.nama as jabatan')
                ->from('t_disposisi_user')
                ->join('left join', 'm_user', 't_disposisi_user.m_user_id = m_user.id')
                ->join('left join', 'm_roles', 'm_user.m_roles_id = m_roles.id')
                ->where('t_disposisi_id', '=', $disposisi->id)
                ->findAll();

            $disposisi->tujuan = $penerima;
        }
        $data->disposisi        = $disposisi;
        $data->disposisi->sifat = ucwords($data->disposisi->sifat);
    }

    /**
     * GET KODE SURAT
     */
    $kode = $db->select('*')->from('m_kode')->findAll();

    /**
     * GET USERS
     */
    $users = $db->select('m_user.id, m_user.nama, m_user.email, m_roles.nama as jabatan')
        ->from('m_user')
        ->join('left join', 'm_roles', 'm_user.m_roles_id = m_roles.id')
        ->where('m_roles_id', '!=', 1)
        ->findAll();

    return successResponse($response, ["list" => $models, "totalItems" => $totalItem, 'kode_surat' => $kode, 'users' => $users]);
});

$app->post('/t_surat_masuk/disposisi/tindak', function ($request, $response) {
    $data = $request->getParams();
    $db   = $this->db;

    try {
        $db->update('t_disposisi_user', ['status' => 1], [
            'm_user_id'      => $_SESSION['user']['id'],
            't_disposisi_id' => $data['disposisi']['id'],
        ]);

        return successResponse($response, 'Data Berhasil ditindaklanjuti');
    } catch (Exception $e) {
        return unprocessResponse($response, $e);
    }
});
