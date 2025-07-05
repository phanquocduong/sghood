document.addEventListener("DOMContentLoaded", function () {
    ClassicEditor.create(document.querySelector("#content"), {
      language: "vi",
      ckfinder: {
       uploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&responseType=json'
      },
    })
      .then((editor) => {
        console.log("CKEditor initialized successfully!", editor);
        editor.ui.view.editable.element.style.height = "400px"; // 👈 chỉnh chiều cao
        editor.ui.view.editable.element.style.width = "100%";   // 👈 chỉnh chiều rộng nếu muốn
      })
      .catch((error) => {
        console.error("Error initializing CKEditor:", error);
      });
  });
document.addEventListener("DOMContentLoaded", function () {
    function openPopup(idobj) {
      CKFinder.popup({
        chooseFiles: true,
        onInit: function (finder) {
          finder.on("files:choose", function (evt) {
            var file = evt.data.files.first();
            document.getElementById(idobj).value = file.getUrl();
          });
          finder.on("file:choose:resizedImage", function (evt) {
            document.getElementById(idobj).value = evt.data.resizedUrl;
          });
        },
      });
    }
  });
