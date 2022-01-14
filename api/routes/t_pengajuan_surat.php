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
        "tgl_surat" => "required",
        "tujuan"    => "required",
        "perihal"   => "required");
    $cek = validate($data, $validasi, $custom);
    return $cek;
}
/**
 * Ambil semuat surat keluar
 */
$app->get("/t_pengajuan_surat/index", function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db; $db->select("t_surat_keluar.*, m_kode.nama, m_kode.kode")
        ->from("t_surat_keluar")
        ->leftJoin('m_kode', 't_surat_keluar.m_kode_id = m_kode.id'); 
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
    if (isset($_SESSION['user']['id'])) {
        $db->where('m_user_id', '=', $_SESSION['user']['id']);
    }
    $db->orderBy('t_surat_keluar.id DESC');
    $models    = $db->findAll();
    $totalItem = $db->count();

    /**
     * GET KODE SURAT
     */
    $kode = $db->select('*')->from('m_kode')->findAll();

    return successResponse($response, [
        "list"       => $models,
        "totalItems" => $totalItem,
        "kode_surat" => $kode,
    ]);
});
/**
 * Savet surat keluar
 */
$app->post("/t_pengajuan_surat/save", function ($request, $response) {
    $param      = $request->getParams();
    $data       = $param['form'];
    $db         = $this->db;
    $validasi   = validasi($data);
    if ($validasi === true) {
        try {
            if (isset($data["id"])) {
                $tgl_surat = Date("Y-m-d", strtotime($data['tgl_surat']));
                $insert = [
                    'tgl_surat'     => $tgl_surat,
                    'tujuan'        => $data['tujuan'],
                    'perihal'       => $data['perihal'],
                    'm_kode_id'     => $data['m_kode_id'],
                    'status'        => $data['status'],
                ];
                if (isset($_FILES['file'])) {
                    $target_dir = "uploads/surat_keluar/";
                    $target_file = $target_dir . date('YmdHis') . '_' . str_replace(' ', '', basename($_FILES["file"]["name"]));
                    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

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
                        
                        // delete data lama
                        $db->select('*')
                            ->from('t_surat_keluar')
                            ->where('id', '=', $data['id']);
                        $dataLama = $db->find();
                        if (file_exists($dataLama->file)) {
                            unlink($dataLama->file);
                        }
                    } else {
                        return unprocessResponse($response, ["gagal upload file"]);
                    }
                }
                $model = $db->update("t_surat_keluar", $insert, ["id" => $data["id"]]);
            } else {
                $tgl_surat = Date("Y-m-d", strtotime($data['tgl_surat']));
                $insert = [
                    'tgl_surat'     => $tgl_surat,
                    'tujuan'        => $data['tujuan'],
                    'perihal'       => $data['perihal'],
                    'status'        => 'request',
                    'm_kode_id'     => $data['m_kode_id'],
                    'm_user_id'     => $_SESSION['user']['id'],
                ];
                if (isset($_FILES['file'])) {
                    $target_dir = "uploads/surat_keluar/";
                    $target_file = $target_dir . date('YmdHis') . '_' . str_replace(' ', '', basename($_FILES["file"]["name"]));
                    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

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
                $model = $db->insert("t_surat_keluar", $insert);
            }
            return successResponse($response, $model);
        } catch (Exception $e) {
            return unprocessResponse($response, $e);
        }
    }
    return unprocessResponse($response, $validasi);
});
/**
 * Hapust surat keluar
 */
$app->post("/t_pengajuan_surat/hapus", function ($request, $response) {
    $data = $request->getParams();
    $db   = $this->db;
    try {
        // delete data file lama
        $db->select('*')
            ->from('t_surat_keluar')
            ->where('id', '=', $data['id']);
        $dataLama = $db->find();
        if (file_exists($dataLama->file)) {
            unlink($dataLama->file);
        }

        $model = $db->delete("t_surat_keluar", ["id" => $data["id"]]);return successResponse($response, $model);
    } catch (Exception $e) {
        return unprocessResponse($response, ["terjadi masalah pada server"]);
    }
    return unprocessResponse($response, $validasi);
});

/**
 * download 
 */
$app->get("/t_pengajuan_surat/download", function ($request, $response) {
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

$app->get("/t_pengajuan_surat/downloadTemplate", function ($request, $response) {
    $fullPath = 'uploads/template_surat_keluar.docx';

    if ($fd = fopen ($fullPath, "r")) {

        $fsize = filesize($fullPath);
        $path_parts = pathinfo($fullPath);
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