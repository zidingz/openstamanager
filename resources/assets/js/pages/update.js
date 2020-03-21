import $ from 'jquery';
import Swal from 'sweetalert2';$("#contine_button").click(function () {
    Swal.fire({
        title: globals.update.translations.title,
        text: "",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-lg btn-success",
        confirmButtonText: globals.update.translations.continue,
    }).then(function () {
        $("#update-info").removeClass("d-none");
        $("#result").load(globals.update.url);
        $("#contine_button").remove();
    }, function () {
    });
});
