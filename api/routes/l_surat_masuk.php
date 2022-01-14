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
        "periode"     => "required",
    );
    $cek = validate($data, $validasi, $custom);
    return $cek;
}
/**
 * Ambil semua t surat masuk
 */
$app->get("/l_surat_masuk/index", function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;
    $validasi = validasi($params);
    try {
        if($validasi !== true) return unprocessResponse($response, $validasi);

        $db->select("t_surat_masuk.*, m_kode.kode, m_kode.nama")
            ->from("t_surat_masuk")
            ->leftJoin('m_kode', 't_surat_masuk.m_kode_id = m_kode.id');
        /**
         * Filter
         */
        if (isset($params['periode']) && !empty($params['periode'])) {
            $month = date("n", strtotime($params['periode']));
            $month = ($month == 12) ? 1 : ($month+1);
            $db->where("MONTH(tgl_surat)", '=', $month);
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
        return successResponse($response, ["list" => $models, "totalItems" => $totalItem]); 
    } catch (Exception $e) {
        return unprocessResponse($response, ["Terjadi masalah pada server : " . $e]);
    }
});