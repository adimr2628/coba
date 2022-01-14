app.controller("tsuratmasukCtrl", function ($scope, Data, $rootScope, Upload, $sce) {
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
        Data.get("t_surat_masuk/index", param).then(function (response) {
            $scope.displayed = response.data.list;
            $scope.kode = response.data.kode_surat;
            $scope.dataUser = response.data.users;
            tableState.pagination.numberOfPages = Math.ceil(
                response.data.totalItems / limit
            );
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

    $scope.create = function (form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = true;
        $scope.formtittle = "Form Tambah Data";
        $scope.form = {};
        $scope.form.tgl_surat = new Date();
        $scope.form.tgl_diterima = new Date();
    };

    $scope.update = function (form) {
        $scope.is_edit = true;
        $scope.is_view = false;
        $scope.is_create = false;
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

    $scope.save = function (form) {
        $scope.loading = true;
        form.tgl_surat = new Date(form.tgl_surat);
        form.tgl_diterima = new Date(form.tgl_diterima);
        
        Upload.upload({
            url: 'http://localhost/ams/api/t_surat_masuk/save',
            data: {
                file: $scope.f, 
                'form': form
            },
        }).then(function onSuccess(result) {
            if (result.data.status_code == 200) {
                $rootScope.alert("Berhasil", "Data berhasil disimpan", "success");
                $scope.cancel();
            } else {
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
            }
        }).catch(function onError(result) {
            $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
        });
        $scope.loading = false;
    };

    $scope.uploadFile = function (file, errFiles) {
        $scope.f = file;
        $scope.errFile = errFiles && errFiles[0];
        if (file) {
        } else {
            $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.errors), "error");
        }
    }

    $scope.cancel = function () {
        $scope.is_edit = false;
        $scope.is_view = false;
        $scope.is_create = false;
        $scope.is_forward = false;
        $scope.formDisposisi = {};
        $scope.callServer(tableStateRef);
    };

    $scope.delete = function (row) {
        if (confirm("Apa anda yakin akan Menghapus item ini ?")) {
            row.is_deleted = 0;
            Data.post("t_surat_masuk/hapus", row).then(function (result) {
                if (result.status_code == 200) {
                    $rootScope.alert("Berhasil", "Data berhasil dihapus", "success");
                    $scope.displayed.splice($scope.displayed.indexOf(row), 1);    
                } else {
                    $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.data.errors) ,"error");
                }
            });
        }
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
            form.disposisi.perintah.forEach(e => {
                $socpe.perintah.selected = e;
            });
        }
        console.log($scope.user);
        $scope.formDisposisi.t_surat_masuk_id = form.id;
    }

    $scope.saveDisposisi = function (form) {
        
        Data.post("t_surat_masuk/disposisi", form).then(function (result) {
            if (result.status_code == 200) {
                $rootScope.alert("Berhasil", "Data berhasil disimpan", "success");
                $scope.cancel();
            } else {
                $rootScope.alert("Terjadi Kesalahan", setErrorMessage(result.errors) ,"error");
            }
        });
    }

    $scope.print = function (form) {
        let id = form.id;
        window.open(
            "api/t_surat_masuk/printDisposisi?id=" + id, "_blank",
            "width=1000,height=700"
        );
    }

    $scope.downloadFile = function (file) {
        window.open("api/t_surat_masuk/download?file=" + file);
    }

});
