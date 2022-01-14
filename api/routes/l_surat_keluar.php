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
        "periode" => "required",
    );
    $cek = validate($data, $validasi, $custom);
    return $cek;
}
/**
 * Ambil semua t surat keluar
 */
$app->get("/l_surat_keluar/index", function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;
    $validasi = validasi($params);
    try {
        if($validasi !== true) return unprocessResponse($response, $validasi);

        $db->select("t_surat_keluar.*, m_kode.kode, m_kode.nama")
            ->from("t_surat_keluar")
            ->leftJoin('m_kode', 't_surat_keluar.m_kode_id = m_kode.id');
        /**
         * Filter
         */
        if (isset($params['periode']) && !empty($params['periode'])) {
            $month = date("n", strtotime($params['periode']));
            $month = ($month == 12) ? 1 : ($month+1);
            $db->where("MONTH(tgl_surat)", '=', $month);
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $db->where('m_user_id', '=', $params['user_id']);
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $db->where('status', '=', $params['status']);
        }
        
        $models    = $db->findAll();
        $totalItem = $db->count();
        return successResponse($response, ["list" => $models, "totalItems" => $totalItem]); 
    } catch (Exception $e) {
        return unprocessResponse($response, ["Terjadi masalah pada server : " . $e]);
    }
});
$app->get('/l_surat_keluar/getPemohon', function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;
    // $validasi = validasi($params);
    try {
        // if($validasi !== true) return unprocessResponse($response, $validasi);

        $db->select("m_user.*, m_roles.nama as hakakses")
            ->from("m_user")
            ->join("left join", "m_roles", "m_user.m_roles_id = m_roles.id")
            ->where('m_user.is_deleted', '=', '0')
            ->where('m_roles.id', '=', '3');
        /**
         * Filter
         */
        if (isset($params["filter"])) {
            $filter = (array) json_decode($params["filter"]);
            foreach ($filter as $key => $val) {
                if ($key == "nama") {
                    $db->where("m_user.nama", "LIKE", $val);
                } else if ($key == "is_deleted") {
                    $db->where("m_user.is_deleted", "=", $val);
                } else {
                    $db->where($key, "LIKE", $val);
                }
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
        foreach ($models as $key => $val) {
            $val->m_roles_id = (string) $val->m_roles_id;
        }
        return successResponse($response, ["list" => $models, "totalItems" => $totalItem]);
    } catch (Exception $e) {
        return unprocessResponse($response, ["Terjadi masalah pada server : " . $e]);
    } 
});
