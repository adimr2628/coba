<?php
/**
 * save user
 */
$app->get("/coba/index", function ($request, $response) {
    $data = $request->getParams();
    $db   = $this->db;

    try {
        if (isset($data["id"])) {
            $model = $db->update("m_user", $data, ["id" => $data["id"]]);
        } else {
            $model = $db->insert("m_kode", [
                'kode'  => 'SK',
                'nama'  => 'Surat Keterangan',
                'desc'  => 'as'
            ]);
        }
        return successResponse($response, $model);
    } catch (Exception $e) {
        return unprocessResponse($response, [$e->getMessage()]);
    }
    return unprocessResponse($response, ['anu']);
});