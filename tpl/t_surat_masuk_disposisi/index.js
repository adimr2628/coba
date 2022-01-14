app.controller("tsuratmasukdisposisiCtrl", function ($scope, Data, $rootScope, Upload, $sce) {
    /**
     * Inialisasi
     */
    var tableStateRef;
    $scope.formtittle = "";
    $scope.displayed = [];
    $scope.form = {};
    $scope.formDisposisi = {};
    $scope.perintah = [
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
        'Hadiri'
    ]
    $scope.is_edit = false;
    $scope.is_view = false;
    $scope.is_create = false;
    $scope.is_forward = false;
    $scope.loading = false;
    $scope.kode = [];
    $scope.dataUser = [];

    /**
     * End inialisasi
     */

    $scope.callServer = function callServer(tableState) {
        tableStateRef = tableState;
        $scope.isLoading = true;
        var offset = tableState.pagination.start || 0;
        var limit = tableState.pagination.number || 10;
        var param = {
            offset: offset,
            limit: limit
        };
        if (tableState.sort.predicate) {
            param["sort"] = tableState.sort.predicate;
            param["order"] = tableState.sort.reverse;
        }
        if (tableState.search.predicateObject) {
            param["filter"] = tableState.search.predicateObject;
        }
        Data.get("t_surat_masuk/disposisi/index", param).then(function (response) {
            $scope.displayed = response.data.list;
            $scope.kode = response.data.kode_surat;
            $scope.dataUser = response.data.users;
            tableState.pagination.numberOfPages = Math.ceil(
                response.data.totalItems / limit
            );
        });
        $scope.isLoading = false;
    };

    $scope.update = function (form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.formtittle = "Edit Data : " + form.no_surat;
        $date = new Date(form.tgl_surat);
        form.tgl_surat = $date;
        $date = new Date(form.tgl_diterima);
        form.tgl_diterima = $date;
        $scope.form = form;
    };

    $scope.view = function (form) {
        $scope.is_edit = true;
        $scope.is_view = true;
        $scope.formtittle = "Lihat Data : " + form.no_surat;
        $date = new Date(form.tgl_surat);
        form.tgl_surat = $date;
        $date = new Date(form.tgl_diterima);
        form.tgl_diterima = $date;
        $scope.form = form;
    };

   

    $scope.cancel = function () {
        $scope.is_edit = false;
        $scope.is_view = false;
        $scope.is_create = false;
        $scope.is_forward = false;
        $scope.formDisposisi = {};
        $scope.callServer(tableStateRef);
    };

  
    $scope.disposisi = function (form) {
        $scope.is_edit = true;
        $scope.is_view = true;
        $scope.is_forward = true;
        $scope.formtittle = "Disposisi Data : " + form.no_surat;

        $date = new Date(form.tgl_surat);
        form.tgl_surat = $date;
        $date = new Date(form.tgl_diterima);
        form.tgl_diterima = $date;
        $scope.form = form;

        if (form.disposisi !== false) {
            $scope.formDisposisi = form.disposisi;
            form.disposisi.tujuan.forEach(e => {
                $scope.dataUser.selected = e;
            });
            console.log(form.disposisi);
            if (form.disposisi.perintah != null) {
                form.disposisi.perintah.forEach(e => {
                    $scope.perintah.selected = e;
                });
            }
        }
        $scope.formDisposisi.t_surat_masuk_id = form.id;
    }

    

    $scope.tindak = function (row) {
        if (confirm("Apakah anda yakin akan Menindaklanjuti surat ini ?")) {
            Data.post("t_surat_masuk/disposisi/tindak", row).then(function (result) {
                if (result.status_code == 200) {
                    $rootScope.alert("Berhasil", "Data berhasil ditindaklanjuti", "success");
                    $scope.cancel();
                } else {
                    $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.errors) ,"error");
                }
            })
        }
    }

    $scope.print = function (form) {
        let id = form.id;
        window.open(
            "api/t_surat_masuk/printDisposisi?id=" + id, "_blank",
            "width=1000,height=700"
        );
    }

    $scope.downloadFile = function (file) {
        window.open("api/t_pengajuan_surat/download?file=" + file);
    }
});
