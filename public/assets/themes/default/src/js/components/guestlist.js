export function sign_guestlist($val){

    $.ajax({
        type: 'POST',
        url: '/core/ajax.guestlist.php',
        data: {
            val: $val
        },
        success: function(response) {

            var commiters = JSON.parse(response);
            var cnt_commit = document.getElementById('nbr-commitments');
            cnt_commit.innerHTML = commiters.evc;

        }
    });
}