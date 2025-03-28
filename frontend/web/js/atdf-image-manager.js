/**
 * ImageManager Class
 * Handles image-related operations including tab loading, class associations, and file uploads
 */
class ImageManager {
    /**
     * Loads image tab content
     * @param {string} tabName - Name of the tab to load
     * @param {number} id - Identifier for the content
     */
    static loadTab(tabName, id) {
        Logger.log(1, 'loadTab', `tabName=${tabName}, id=${id}`);

        $("#currentTab").html(tabName);
        $("#currentId").html(id);
        $("#container").html(`ajax-${tabName}`);

        TableManager.loadGenericAjaxTable(0);
    }

    /**
     * Sets image class association
     * @param {number} imageId - Image identifier
     * @param {number} classId - Class identifier
     * @param {string} className - Class name
     */
    static setClass(imageId, classId, className) {
        Logger.log(1, 'setClass', `imageId=${imageId}, classId=${classId}, className=${className}`);

        const inputControl = $(`#image-${imageId}-${classId}`);
        if (!inputControl)
            return;

        const checked = inputControl.is(":checked");
        const status = checked ? 1 : 0;

        Logger.log(10, 'setClass', `checked=${checked}, status=${status}`);

        AjaxUtils.request({
            url: 'image/ajax-set-class',
            data: {
                imageId: imageId,
                classId: classId,
                status,
                className
            },
            successCallback: (response) => {
                ToastManager.show(
                        response.error ? "Error" : "User role",
                        response.msg,
                        response.error ? 'error' : 'info'
                        );
            }
        });
    }

    /**
     * Handles image upload process
     */
    static upload() {
        Logger.log(1, 'upload', '');

        const category = $("input:radio[name=image-upload-category]:checked").val();
        const fileName = $("#uploadFileName").val().split('\\').pop();

        Logger.log(1, 'upload', `category=${category}, fileName=${fileName}`);
    }

    /**
     * Prepares upload functionality and UI
     */
    static prepareUpload() {
        Logger.log(1, 'prepareUpload', '');

        const btnUpload = $("#uploadFileName");
        const btnOuter = $(".button_outer");

        btnUpload.on("change", (e) => {
            const ext = btnUpload.val().split('.').pop().toLowerCase();
            const allowedExtensions = ['gif', 'png', 'jpg', 'jpeg'];

            if (!allowedExtensions.includes(ext)) {
                $(".error_msg").text("Not an Image...");
                return;
            }

            $(".error_msg").text("");
            const uploadedFile = URL.createObjectURL(e.target.files[0]);
            Logger.log(1, 'prepareUpload', `uploadedFile=${uploadedFile}`);

            $("#uploadedFilePreview")
                    .append(`<img src="${uploadedFile}" />`)
                    .addClass("show");
        });

        $(".file_remove").on("click", () => {
            $("#uploadedFilePreview")
                    .removeClass("show")
                    .find("img")
                    .remove();
            btnOuter.removeClass("file_uploaded");
        });
    }
}
