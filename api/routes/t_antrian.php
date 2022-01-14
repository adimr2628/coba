<?php
$app->get("/t_antrian/index", function ($request, $response) {
    $db = $this->db;
    $models = getAntrian();
    $totalItems = count($models);

    /**
     * GET KODE SURAT
     */
    $kode = $db->select('*')->from('m_kode')->findAll();

    return successResponse($response, [
        "list"      => $models,
        "kode_surat"=> $kode,
        'totalItems'=> $totalItems,
    ]);
});

$app->post("/t_antrian/import", function ($request, $response) {
    $email = new Imap();
    $email->connect(
            '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', 
            'mandiri.nusantara08@gmail.com',
            'MANUS008xyz'
        );
    $params = $request->getParams();
    $db = $this->db;

    try {
        $data = [
            'id_gmail'      => $params['uid'],
            'source'        => "gmail",
            'no_surat'      => $params['no_surat'],
            'tgl_surat'     => date("Y-m-d", strtotime($params['tgl_surat'])),
            'tgl_diterima'  => date("Y-m-d"),
            'dari'          => $params['from_name'],
            'perihal'       => $params['perihal'],
            'm_kode_id'     => $params['m_kode_id'],
            'm_user_id'     => $_SESSION['user']['id'],
        ];
        if (isset($params['is_import']) && !empty($params['is_import'])) {
            $param = [
                'uid'       => $params['uid'],
                'part'      => $params['is_import']['part'],
                'file'      => $params['is_import']['file'],
                'encoding'  => $params['is_import']['encoding']
            ];
            $file = $email->getFiles($param);
            $data['file'] = $file['path2'];
        }
        $model = $db->insert('t_surat_masuk', $data);

        return successResponse($response, $model);
    } catch (Exception $e) {
        return unprocessResponse($response, [$e->getMessage()]);
    }
});

$app->post("/t_antrian/getFile", function ($request, $response) {
    $email = new Imap();
    $email->connect(
            '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', //host
            // 'hello.mandirinusantara@gmail.com', //username
            // 'manus019' //password
            'mandiri.nusantara08@gmail.com',
            'MANUS008xyz'
        );
    $param = $request->getParams();
    $inbox = $email->getFiles($param);
    return successResponse($response, $inbox);
});

$app->get('/t_antrian/refreshAntrian', function ($request, $response) {
    $db = $this->db;
    try {
        /**
         * Ambil semua surat masuk dari gmail
         */
        $email = new Imap();
        $connect = $email->connect(
            '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX', //host
            // 'hello.mandirinusantara@gmail.com', //username
            // 'manus019' //password
            'mandiri.nusantara08@gmail.com',
            'MANUS008xyz'
        );

        // inbox array
        $antrian = $email->getMessages('html');

        // jika ada surat masuk di email
        if ($antrian) {
            // hapus semua data antrian di db
            $db->run("TRUNCATE TABLE t_antrian_surat");
            $db->run("TRUNCATE TABLE t_antrian_surat_attacments");

            // masukkan data ke db
            foreach ($antrian['data'] as $key => $value) {
                $insertAntrian = [
                    'uid'           => $value['uid'],
                    'subject'       => @$value['subject'],
                    'from_address'  => @$value['from']['address'],
                    'from_name'     => @$value['from']['name'],
                    'message'       => @$value['message'],
                    'date'          => @$value['date']
                ];
                $db->insert('t_antrian_surat', $insertAntrian);

                if (!empty($value['attachments'])) { // jika punya attachment masukan ke db
                    foreach ($value['attachments'] as $k => $val) {
                        $insertAttachment = [
                            'uid'       => $value['uid'],
                            'file'      => $val['file'],
                            'part'      => $val['part'],
                            'encoding'  => $val['encoding']
                        ];
                        $db->insert('t_antrian_surat_attacments', $insertAttachment);
                    }
                }
            }

            // ambil antrian yang sudah di filter
            $models = getAntrian();
            $totalItems = count($models);

            return successResponse($response, [
                "list"       => $models,
                'totalItems' => $totalItems,
            ]);
        } else {
            $models = getAntrian();
            $totalItems = count($models);

            return successResponse($response, [
                "list"       => $models,
                'totalItems' => $totalItems,
            ]);
        }
    } catch (Exception $e) {
        return unprocessResponse($response, $e);
    }
});

function getAntrian() {
    $config = config('DB');
    $db = new Cahkampung\Landadb($config['db']);

    // ambil data yang sudah ter-import dari db
    $surat_masuk = $db->select('*')
        ->from('t_surat_masuk')
        ->where('source', '=', 'gmail')
        ->findAll();
    $imported = [];
    foreach ($surat_masuk as $data) {
        $imported[] = $data->id_gmail;
    }

    // ambil data antrian yang belum terfilter dari table
    $antrian = $db->select('*')
        ->from('t_antrian_surat')
        ->findAll();

    // ambil attachment dari antrian
    foreach ($antrian as $key => $value) {
        $attachments = $db->select('*')
            ->from('t_antrian_surat_attacments')
            ->where('uid', '=', $value->uid)
            ->findAll();
        $antrian[$key]->attachments = ($attachments) ? $attachments : []; // jika tidak ada set nilai array kosongan
    }

    // filter yang sudah terimport dan tidak
    $models = [];
    foreach ($antrian as $data) {
        if (!in_array($data->uid, $imported)) {
            foreach ($data->attachments as $key => $file) {
                $data->attachments[$key]->is_import = 0;
            }
            $models[] = $data;
        }
    }

    return $models;
}