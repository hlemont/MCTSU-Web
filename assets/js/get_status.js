function refresh_button(data){
    var params = {
        location: location.pathname.substr(1)
    }

    if(data['login_status']) {
        $("a.personal").text("내 프로필");
        $("a.personal").attr("href", "/profile.html?" + $.param(params));
    }
    else {
        params['action'] = 'login';
        $("a.personal").text("함께하기");
        $("a.personal").attr("href", "/login.php?" + $.param(params));								
    }
}

function refresh_icon(data, target){
    console.log(data['login_status']);
    if(data['login_status']){
        $("i." + target).removeClass('fa-times-circle notice-negative')
        $("i." + target).addClass('fa-check-circle notice-positive active');
    }
    else{
        $("i." + target).removeClass('fa-check-circle notice-positive');
        $("i." + target).addClass('fa-times-circle notice-negative active');
    }
}


//overloading
function refresh_icon_discord(data){
    refresh_icon(data, 'discord-status');
}

function refresh_icon_profile(data){
    refresh_icon(data, 'profile-status');
}

function refresh_icon_verify(data){
    refresh_icon(data, 'verify-status');
}


function check_discord(func){
    $.ajax({
        url: 'login.php',
        type: "GET",
        data: {action: 'check'},
        dataType: 'json',
        contentType: "application/json",
        success: func
    });
}

function check_profile(func){
    $.ajax({
        url: 'profile.php',
        type: "GET",
        data: {action: 'check'},
        dataType: 'json',
        contentType: "application/json",
        success: func
    });
}

function check_verified(func){
    $.ajax({
        url: 'verify.php',
        type: "GET",
        data: {action: 'check'},
        dataType: 'json',
        contentType: "application/json",
        success: func
    });
}


