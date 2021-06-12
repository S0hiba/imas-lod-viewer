$(function() {
    $.extend( $.fn.dataTable.defaults, {
        language: {
            url: "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Japanese.json"
        }
    });

    $('#data_table').DataTable({
        lengthChange: false,
        paging: false
    });

    $.ajax({
        type: 'POST',
        url: '/viewer/ajax/checkNewData/',
        data: {
            endPointName: 'imasparql'
        }
    })
        .done(function(data) {
        });
});
