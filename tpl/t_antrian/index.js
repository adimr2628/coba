app.controller("tantrianCtrl", function($scope, Data, $rootScope, Upload, $sce) {
    /**
     * Inialisasi
     */
    var tableStateRef;
    $scope.formtittle = "";
    $scope.displayed = [];
    $scope.form = {};
    $scope.kode_surat = {};
    $scope.is_edit = false;
    $scope.is_view = false;
    $scope.is_create = false;
    $scope.loading = false;
    /**
     * End inialisasi
     */
    $scope.callServer = function callServer(tableState) {
        Data.get("t_antrian/index", '').then(function(response) {
            $scope.displayed = response.data.list;
            $scope.displayed.forEach((item, index) => {
                $sce.trustAsHtml(item.message);
            })
            $scope.kode = response.data.kode_surat;
            $scope.lastUpdate = response.data.list[0].created_at * 1000;
        });
        $scope.isLoading = false;
    };

    $scope.clearFilter = function (filter) {
        var param = tableStateRef.search.predicateObject;
        if (filter == 'tgl_surat') {
            param.tgl_surat = "";
        }else if (filter == 'tgl_diterima') {
            param.tgl_diterima = "";
        }
        tableStateRef.search.predicateObject = param;
        $scope.callServer(tableStateRef);
    }

    $scope.detail = function (form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = true;
        $scope.form = form;
        $scope.message = $sce.trustAsHtml(form.message);;
        $scope.formtittle = form.subject;
    }

    $scope.create = function(form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = true;
        $scope.formtittle = "Form Tambah Data";
        $scope.form = {};
    };

    $scope.update = function(form) {
        $scope.is_edit      = true;
        $scope.is_create    = false;
        $scope.is_view      = false;
        $scope.formtittle   = "Edit Data : " + form.no_surat;
        $scope.form         = form;
    };

    $scope.view = function(form) {
        $scope.is_edit      = true;
        $scope.is_create    = false;
        $scope.is_view      = true;
        $scope.formtittle   = "Lihat Data : " + form.no_surat;
        $scope.form         = form;
    };

    $scope.import = function(form) {
        $scope.loading = true;
        form.tgl_surat = new Date(form.tgl_surat);
        Data.post("t_antrian/import", form).then(function(result) {
            if (result.status_code == 200) {
                let newData = $scope.displayed.filter(el => el.uid !== form.uid);
                $scope.displayed = newData;
                $rootScope.alert("Berhasil", "Data berhasil disimpan", "success");
                $scope.cancel();
            } else {
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.errors) ,"error");
            }
            $scope.loading = false;
        });
        $scope.loading = false;
    };

    $scope.cancel = function() {
        $scope.is_edit = false;
        $scope.is_view = false;
        $scope.is_create = false;
        // $scope.callServer(tableStateRef);
    };

    $scope.getFile = function (row, uid) {
        let data = {
            uid     : uid,
            part    : row.part,
            file    : row.file,
            encoding: row.encoding
        }
        Data.post("t_antrian/getFile", data).then(function (response) {
           window.open(response.data.path, '_blank'); 
        });
    }

    $scope.is_import = function (row, uid) {
        if (row.is_import) {
            $scope.form.is_import = row
        } else {
            $scope.form.is_import = 0;
        }
        $scope.form.attachments.forEach((item, index) => {
            if (item.file != row.file && item.part != row.path) {
                item.is_import = 0;
            }
        })
    }

    $scope.refreshAntrian = function () {
        Data.get("t_antrian/refreshAntrian", '').then(function(response) {
            $scope.displayed = response.data.list;
            $scope.displayed.forEach((item, index) => {
                $sce.trustAsHtml(item.message);
            })
            $scope.kode = response.data.kode_surat;
            $scope.lastUpdate = response.data.list[0].created_at * 1000;
        });
    }
});