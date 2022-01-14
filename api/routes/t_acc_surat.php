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
$app->get("/t_acc_surat/index", function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;

    $db->select("t_surat_keluar.*, m_kode.nama, m_kode.kode, m_user.nama as pengaju")
        ->from("t_surat_keluar")
        ->leftJoin('m_kode', 't_surat_keluar.m_kode_id = m_kode.id')
        ->leftJoin('m_user', 't_surat_keluar.m_user_id = m_user.id')
        ->where('status', '=', 'request')
        ->orWhere('status', '=', 'acc');
    /**
     * Filter
     */
    if (isset($params["filter"])) {
        $filter = (array) json_decode($params["filter"]);
        foreach ($filter as $key => $val) {
            $db->andWhere($key, "LIKE", $val);
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

$app->post('/t_acc_surat/acc', function ($request, $response) {
    $params = $request->getParams();
    $db     = $this->db;

    try {
        $update = $db->update('t_surat_keluar', [
            'status' => $params['status'],
            'ket'    => $params['ket']
        ], ['id' => $params['id']]);

        // jika acc surat keluar
        if ($params['status'] == 'acc') {
            $no_surat = gen_no_surat($params['m_kode_id']);
            $update = $db->update('t_surat_keluar', ['no_surat' => $no_surat], ['id' => $params['id']]);
        }
        return successResponse($response, "Data Berhasil Disetujui");
    } catch (Exception $e) {
        return successResponse($response, $e);
    }
});
