jQuery.fn.exists = function () {
    return jQuery(this).length > 0;
}

jQuery(document).ready(function($) {

    if($(".plupload-upload-uic").exists()) {
        var pconfig=false;
        $(".plupload-upload-uic").each(function() {
            var $this=$(this);
            var id1=$this.attr("id");
            var xmlId=id1.replace("plupload-upload-ui", "");

            pconfig=JSON.parse(JSON.stringify(base_plupload_config));

            pconfig["browse_button"] = xmlId + pconfig["browse_button"];
            pconfig["container"] = xmlId + pconfig["container"];
            pconfig["drop_element"] = xmlId + pconfig["drop_element"];
            pconfig["file_data_name"] = xmlId + pconfig["file_data_name"];
            pconfig["multipart_params"]["xmlId"] = xmlId;
            pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");

            if($this.hasClass("plupload-upload-uic-multiple")) {
                pconfig["multi_selection"]=true;
            }
            var uploader = new plupload.Uploader(pconfig);

            uploader.bind('Init', function(up){

            });

            uploader.init();

            // a file was added in the queue
                uploader.bind('FilesAdded', function(up, files){
                    $.each(files, function(i, file) {
                        $this.find('.filelist').append(
                            '<div class="file" id="' + file.id + '"><b>' +

                            file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' +
                            '<div class="fileprogress"></div></div>');
                    });

                    up.refresh();
                    up.start();
                });

            uploader.bind('UploadProgress', function(up, file) {

                $('#' + file.id + " .fileprogress").width(file.percent + "%");
                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });

            // a file was uploaded
            uploader.bind('FileUploaded', function(up, file, response) {

                $('#' + file.id).fadeOut();
                response=response["response"]
                // add url to the hidden field
                if($this.hasClass("plupload-upload-uic-multiple")) {
                    // multiple
                    var v1=$.trim($("#" + xmlId).val());
                    if(v1) {
                        v1 = v1 + "," + response;
                    }
                    else {
                        v1 = response;
                    }
            $("#" + xmlId).val(v1);
                }
                else {
                    // single
                    $("#" + xmlId).val(response + "");
                }

            });
        });
    }
});
