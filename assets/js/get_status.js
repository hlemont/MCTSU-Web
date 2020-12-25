function refresh_button(data){
    var params = {
        location: location.pathname
    }

    if(data['login_status']) {
        $("a.personal").text("내 프로필");
        $("a.personal").attr("href", "/profile");
    }
    else {
        params['action'] = 'login';
        $("a.personal").text("함께하기");
        $("a.personal").attr("href", "/login.php?" + $.param(params).substr(1));								
    }
}

function refresh_icon_discord(data){
    if(data['login_status']){
        $("i.discord-status").removeClass('fa-times-circle notice-negative')
        $("i.discord-status").addClass('fa-check-circle notice-positive');
    }
    else{
        $("i.discord-status").removeClass('fa-check-circle notice-positive');
        $("i.discord-status").addClass('fa-times-circle notice-negative');
    }
}

function check_status(func){
    $.ajax({
        url: 'login.php',
        type: "GET",
        data: {action: 'check'},
        dataType: 'json',
        contentType: "application/json",
        success: func
    });
}