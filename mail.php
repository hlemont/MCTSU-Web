<?php 
require('users.php')
$mc_secret = 'MCseKretTsuaDmiNi';



if($_SERVER['REQUEST_METHOD'] == 'GET') {

    if(isset($_POST['action'])){
        // add to mail notice table
        if($_GET['action'] == 'notice'){
            $id = $_SESSION['id'];
            $email_addr = $_GET['email'];


            if(add_notification($id, $email_addr)){
                $result = array(
                    status => 'success'
                );
            }
            else{
                $result = array(
                    status => 'failure',
                    error => ['DBError', database]
                );
            }

            $json = json_encode($result);
            if($json === false) {
                $json = json_encode(array("jsonError", json_last_error_msg()));
                http_response_code(500);
            }
            echo $json;
            die();       
        }

        // server secrets
        if(isset($_GET['secret']) && $_GET['secret'] == $mc_secret){
            // mail verify request
            if($_GET['action'] == 'request'){
                // check if requested mc_name equals with db's mc_name
                $data = select_account_by_id($_GET['id']);
                if($data){
                    if($_GET['mc_name'] == $data['mc_name']){
                        // get uuid from mc_name
                        $data = mc_name_to_uuid($_GET['mc_name']);
                        if($data['status'] == 'success'){
                            $uuid = $data['uuid'];

                            // send mail
                            $to = "hlemont01@gmail.com";
                            $subject = "MCTSU 인증 요청";
                            $param = array(
                                action => 'verify',
                                id => $data['id'],
                                mc_name => $data['mc_name'];
                            )
                            $link = "https://web.mctsu.kr/mail.php" . http_build_query($param);
                            
                            $file = fopen("assets/mail/verify_request.html");
                            $template = fread($file);
                            $fclose($file);

                            if(send_email($to, $subject, $template, array(
                                $link, $data['discord_name'], $data['mc_name'];
                            )
                            )){
                                $result = array(
                                    status => 'success'
                                );
                                $result = array(
                                    status => 'failure',
                                    error => ['MailError', error_get_last()]
                                );
                            }
                        }
                        else{
                            $result = array(
                                status => 'failure',
                                error => $data['error'];
                            );
                        }
                    }
                    else{
                        $result = array(
                            status => 'failure',
                            error => ['NameError', 'mc_name does not match']
                        );
                    }
                }
                else{
                    $result = array(
                        status => 'failure',
                        error => ['DBError']
                    );
                }

                $json = json_encode($result);
                if($json === false) {
                    $json = json_encode(array("jsonError", json_last_error_msg()));
                    http_response_code(500);
                }
                echo $json;
                die();
            }



            // verify operation from mail
            if($_GET['action'] == 'verify'){

                $account = select_account_by_id($_GET['id']);
                if($account){
                    if($_GET['mc_name'] != )
                    // verify
                    $output = '';
                    exec("/home/serveradmin/whitelist.sh add {$_GET['mc_name']}");

                    $emails = get_notification($_GET['id']);
                    if($emails){
                        // send_email
                        foreach($emails as $email){
                            $to = $email['email_addr'];
                            $subject = "MCTSU 가입 인증이 완료되었습니다.";

                            $file = fopen("assets/mail/verify_notice.html");
                            $template = fread($file);
                            $fclose($file);

                            $server_name = "MCTSU Survival";
                            $server_addr = "minecraft.mctsu.kr";
                            send_email($to, $subject, $template, array(
                                $server_name, $server_addr, $account['discord_name'], $account['mc_name'];
                            ));
                        }
                        if(remove_notification($_GET['id'])){
                            $result = array(
                                status => 'success'
                            );
                        }
                        else(
                            $result = array(
                                status => 'failure',
                                error => ['DBError', 'remove']
                            );
                        )
                    }
                    else{
                        $result = array(
                            status => 'failure',
                            error => ['DBError', 'select']
                        );
                    }
                }
                else{
                    $result = array(
                        status => 'failure',
                        error => ['DBError', 'select']
                    );
                }
            }
        }
       
    }
}

function show_alert($result, $success_message, $failure_message){
    header('Content-Type: text/html');
    if($result['status'] == 'success'){
        // show success message, redirect back
        echo "<script>alert('{$success_message}'); document.referrer?history.back():location.href='http://m.naver.com';";
    }
    else{
        // show failure message, redirect back
        echo "<script>alert('{$failure_message}'); document.referrer?history.back():location.href='http://m.naver.com';";
    }
    die();
}

function send_email($to, $subject, $template, $parameter=[]) {
    // send mail
    
    $message = vsprintf($template, $parameter);
    
    //headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: admin@web.mctsu.kr' . "\r\n";

    return mail($to, $subject, $message, $headers);
}


?>
