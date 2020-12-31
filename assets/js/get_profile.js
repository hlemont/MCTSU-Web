target_list = ['username', 'discord_name', 'mc_name', 'twitch_name'];

function load_profile(){
    $.ajax({
        url: 'profile.php',
        type: 'GET',
        data: {action: 'load'},
        dataType: 'json',
        contentType: 'application/json',
        success: function(data){
            if(data['status'] == 'success'){
                for(let target in target_list){
                    document.getElementById(target).value = data[target];
                }
            }
            else{
                alert('프로필을 로드하는 중 오류가 발생했습니다: ' + data['error']);
            }
        }
    });
}

function save_profile(){
    $.ajax({
        url: 'profile.php',
        type: 'GET',
        data: {action: 'save', ...target_list},
        dataType: 'json',
        contentType: 'application/json',
        success: function(data){
            if(data['status'] == 'success'){
                alert('성공적으로 프로필 정보를 저장했습니다.');
            }
            else{
                alert('프로필을 저장하는 중 오류가 발생했습니다: ' + data['error']);
            }
        }
    });
}




