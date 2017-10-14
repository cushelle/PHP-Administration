<?php
    session_start(); 

    require_once("configuration.php");
    require_once("php/manage/functions.php");


    if (!isset($_SESSION['LoggedIn'])){
        header("Location: login.php");
    }
    
    $connection = new PDO("mysql:dbname=".DB_NAME.";host=".HOST, USERNAME, PASSWORD);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Administrate</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <style type="text/css">
        #chatmodal-body {
          max-height: 400px;
          overflow-y: auto;
        }

        #serverchatmodal-body {
          max-height: 600px;
          overflow-y: auto;
          z-index:10000000;
        }
    </style>

</head>
<body>
    <?php if (isset($_SESSION['LoggedIn'])){ ?>
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                     <li class="dropdown">
                      <a class="dropdown-toggle navbar-brand" data-toggle="dropdown" href="#"><?php echo($_SESSION['username']); ?></a>
                      <ul class="dropdown-menu" style='width:200px'>
                        <li><a href="index.php">Requests<span class='pull-right glyphicon glyphicon-comment' style='margin-top:2px'></span></a></li>
                        <li><a href="#" data-toggle="modal" data-target="#settingsmodal">Account Settings<span class='pull-right glyphicon glyphicon-cog dropdown-margin' style='margin-top:3px;'></span></a></li>
                        <li><a href='php/logout.php'>Logout<span class='glyphicon glyphicon-log-out pull-right'></span></a></li>
                      </ul>
                    </li>
                    <li>
                    <a href="#">Administrate</a>
                    </li>
                     <li class="dropdown">
                      <a class="dropdown-toggle" data-toggle="dropdown" href="#">Manage<span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li><a href="php/manage/contacts.php">Admins and Contacts</a></li>
                        <li><a href="php/manage/servers.php">Servers</a></li>
                        <li><a href="php/manage/ranks.php">Ranks</a></li> 
                      </ul>
                    </li>
                    <li class="dropdown">
                      <a class="dropdown-toggle" data-toggle="dropdown" href="#">Chat<span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li><a href="#" data-toggle="modal" name='open-server-chat' data-target="#serverchatmodal">Server Chat</a></li>
                        <li><a href="#" data-toggle="modal" name='open-chat' data-target="#chatmodal">Admin Chat</a></li>
                      </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class='container'>
          <div class='row' style='margin-top:70px'>
            <div class='col-lg-12'>
                <div class='well well-lg'>
                    <h1>Administrate Your Servers</h1>
                     <p>Here you can view online players, as well as kick or ban players. <?php if (hasPermission($_SESSION['ID'], 'CanRCONExec')){echo("You can also restart servers and execute RCON commands");} ?></p>
                </div>
                <noscript><div class="alert alert-warning">Turn on JavaScript to get the best experience</div></noscript>
                <div id='kick-result'></div>


                <!-- Modal -->
                <div class="modal fade" id="settingsmodal" tabindex="-1" role="dialog" aria-labelledby="settingsmodal" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id='self-update-form' class="form-horizontal">
                        <div class="modal-content">
                            <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                            <h4 class="modal-title" id="myModalLabel">Account Settings</h4>
                            </div>
                            <div class="modal-body">
                                <div id="selfupdate-result"></div>
                                <?php 
                                    $stmt = $connection->prepare("SELECT * FROM `admins` WHERE `ID` = :sessionid");
                                    $stmt->bindParam(":sessionid", $_SESSION['ID']);

                                    if ($stmt->execute()){
                                        $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
                                    }
                                ?>
                                 <div class="form-group">
                                   <div class="col-sm-6">
                                     <label for="Username" class="sr-only">Username</label>
                                     <input class="form-control input-group-lg reg_name" value="<?php echo($currentAdmin['Username']) ?>" type="text" name="self-username" placeholder="Username">
                                   </div>       
                                   <div class="col-sm-6">
                                        <label for="contact" class="sr-only"></label>
                                        <input class="form-control input-group-lg" value="<?php echo($currentAdmin['Email']) ?>" type="text" name="self-contact" placeholder="Email / Phone Number">
                                        <br>
                                   </div>
                                   <div class="col-sm-6">
                                        <label for="password" class="sr-only"></label>
                                        <input id="password" class="form-control input-group-lg" type="password" name="self-password" placeholder="Password">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="self-new-password" class="sr-only"></label>
                                        <input class="form-control input-group-lg" type="password" name="self-new-password" placeholder="New Password">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Receive request notifications:
                                        <select data-toggle='popover' data-content='Should you receive an email/text message when someone requests an admin?'  class="form-control input-group-lg pull-right" name="self-new-shouldcontact">
                                            <?php
                                                if ($currentAdmin['ShouldContact'] == 1){
                                                    echo("<option value='1'>Yes</option><option value='0'>No</option>");
                                                }
                                                else{
                                                    echo("<option value='0'>No</option><option value='1'>Yes</option>");                                                    
                                                }
                                                echo($option);
                                            ?>
                                        </select>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </form>
                        </div>
                    </div>
                  </div>
                </div>
                <!--/modal-->



                <!-- Chat modal -->
                <div class="modal fade" id="chatmodal" tabindex="-1" role="dialog" aria-labelledby="chatmodal" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                                <h4 class="modal-title" id="myModalLabel">Chat</h4>
                            </div>
                            <div class="modal-body" id='chatmodal-body'>
                                <div id="chat-response"></div>
                                <div id="chat-reload" name='chat-reload'>
                                <?php
                                    $stmt = $connection->prepare("SELECT * FROM `adminchat`");
                                    $stmt->execute();

                                    while ($message = $stmt->fetch(PDO::FETCH_ASSOC)){
                                        if ($message['From'] == $_SESSION['username']){
                                            echo("<h4><label class='label label-primary'>". htmlspecialchars($message['From']) .":</label></h4>");
                                        }
                                        else{
                                            echo("<h4><label class='label label-default'>". htmlspecialchars($message['From']) .":</label></h4>");
                                        }
                                       echo("<div class='well well-lg'><p>".htmlspecialchars($message['Message']) ."</p><strong><small>".$message['Time']."</small></strong></div><hr>");
                                    }
                                ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <form name='admin-chat-form'>
                                    <div class="input-group">
                                        <input type='text' name='admin-chat-message' class='form-control'>
                                        <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">Send</button>
                                      </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="serverchatmodal" tabindex="-1" role="dialog" aria-labelledby="serverchatmodal" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                                <h4 class="modal-title">Server Chat</h4>
                                <select name='show-chat-from-server' class='form-control'>
                                    <option value="All">All</option>
                                    <?php
                                        $stmt = $connection->prepare("SELECT * FROM `servers`");
                                        $stmt->execute();
                                        while ($server = $stmt->fetch(PDO::FETCH_ASSOC)){
                                            echo("<option value='".$server['ID']."'>".$server['Name']."</option>");
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="modal-body" id='serverchatmodal-body'>
                                <div id="server-chat-response"></div>
                                <div id="serverchat-reload" name='serverchat-reload'>
                                    
                                </div>
                            </div>
                            <div class="modal-footer">
                                <form name='server-chat-form'>
                                    <div class="input-group">
                                        <input type='text' name='server-chat-message' class='form-control'>
                                        <span class="input-group-btn">
                                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            Send To <span class="caret"></span>
                                          </button>
                                          <ul class="dropdown-menu" role="menu">
                                            <?php
                                                $stmt = $connection->prepare("SELECT * FROM `servers`");
                                                $stmt->execute();
                                                $server = $stmt->fetch(PDO::FETCH_ASSOC);

                                                echo("<li><a name='send-chat-to-server' data-id='". $server['ID'] ."' href='#'>". $server['Name'] ."</a></li>");
                                            ?>
                                          </ul>             
                                      </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <hr>
                <div name='update' id='update'>
                <?php
                    require_once("php/SourceQuery/SourceQuery.class.php"); 
                    $Server = new SourceQuery();

                    # Creates server tables that lists players, along with options to kick or ban them if they have permission
                    $sql = "SELECT * FROM `servers`";
                    $stmt =  $connection->prepare($sql);
                    $stmt->execute();
                    $Query = new SourceQuery();
                    try{
                        while ($server = $stmt->fetch(PDO::FETCH_ASSOC)){
                            echo("<div class='well panel panel-primary' style='padding:0px;'><div class='panel-heading'><h1 class='panel-title' style='font-size: 24px'>". htmlspecialchars($server['Name']) ."<a href='#'><span data-toggle='popover' data-content='Refresh Players' class='glyphicon glyphicon-refresh pull-right' style='margin-top:2px'></span></a></h1></div><div class='panel-body' style='padding-top:0px; padding-left:0px; padding-right:0px'>");
                            
                            $servers_table = "<table class='table table-condensed table-hover table-bordered'><tr><th>Player Name <span class='glyphicon glyphicon-user'></span></th><th>Steam ID</th><th>Time Online <span class='glyphicon glyphicon-time'></span>";
                      
                            if (hasPermission($_SESSION['ID'], 'CanKick')){
                                $servers_table .= "<th>Kick</th>";
                            }
                            if (hasPermission($_SESSION['ID'], 'CanBan')){
                                $servers_table .= "<th>Ban</th>";
                            }
                            
                            $servers_table .= "</tr>";

                            $Query->Connect($server['IP'], $server['Port'], 1, SourceQuery::SOURCE);
                            $info = $Query->GetInfo();
                            $Query->SetRconPassword($server['RCON']);
                            $status = $Query->Rcon("status");

                            if (!empty($info)){
                                foreach($Query->GetPlayers() as $key => $value){ 
                                    
                                    if (preg_match('~"' . preg_quote($value['Name']) . '"\s+(.*?)\s~', $status, $steamid)){
                                        $SteamID = $steamid[1];
                                    }
                                    else{
                                        $SteamID = "Unavailable";
                                    }

                                    $name = empty($value['Name']) ? "(Player is Connecting...)" : $value['Name'];
                                    $servers_table .= "<tr><td><h4><strong>". $name ."</strong></h4></td><td><h4><strong>". $SteamID ."</strong></h4></td><td><h4><strong>". gmdate("H:i", $value['Time']) ."</strong></h4></td>";
                                    
                                    if (hasPermission($_SESSION['ID'], 'CanKick')){
                                      $servers_table .= "<td class='dropdown'><a class='dropdown-toggle' href='#' data-toggle='dropdown'><button class='btn btn-warning'>Kick</button></a>
                                                            <div class='dropdown-menu' style='padding:15px; width:340px'>
                                                                <div class='form-group'>
                                                                    <form id='kickplayer' class='kick-form'>
                                                                        <label for='kick-reason'>Reason: </label>
                                                                        <input class='form-control' id='kick-reason' name='kick-reason'>
                                                                        <input type='hidden' id='kick-player' name='kick-player' value='". $value['Name'] ."''>
                                                                        <input type='hidden' id='kickplayer-rcon-hash' name='kickplayer-rcon-hash' value='". $server['RCONHash'] ."'>
                                                                        <input type='hidden' id='server-identifier' name='kickplayer-server-identifier' value='". $server['ID'] ."'>
                                                                        <br>
                                                                        <input type='submit' id='kick-submit' name='kick-submit' value='Kick ". $value['Name'] ."' class='btn btn-default'>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </td>";
                                    }

                                    if (hasPermission($_SESSION['ID'], 'CanBan')){
                                      $servers_table .= "<td class='dropdown'><a class='dropdown-toggle' href='#' data-toggle='dropdown'><button class='btn btn-danger'>Ban</button></a>
                                                            <div class='dropdown-menu' style='padding:15px; width:340px'>
                                                                <div class='form-group'>
                                                                    <form id='banplayer' class='form-controls'>
                                                                        <label for='ban-reason'>Reason: </label>
                                                                        <input class='form-control' name='ban-reason'>
                                                                        <label for='ban-time'>Time: </label>
                                                                        <div class='form-inline'>
                                                                        <input class='form-control' style='width:150px;'name='ban-time'>
                                                                        <select class='form-control' style='width:150px;' name='ban-time-t'>
                                                                            <option value='Minutes'>Minutes</option>
                                                                            <option value='Hours'>Hours</option>
                                                                            <option value='Days'>Days</option>
                                                                        </select>
                                                                        <input type='hidden' name='ban-player' value='". $value['Name'] ."''>
                                                                        </div>
                                                                        <br>
                                                                        <input type='hidden' name='banplayer-rcon-hash' value='". $server['RCONHash'] ."'>
                                                                        <input type='hidden' name='banplayer-server-identifier' value='". $server['ID'] ."'>
                                                                        <input type='submit' name='ban-submit' value='Ban ". $value['Name'] ."' class='btn btn-default'>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </td>";
                                    }
                                    $servers_table .= "</tr>";
                                }
                                $servers_table .= "</table><h2>";

                                $servers_table .= "</div></div>"; 
                                if (hasPermission($_SESSION['ID'], 'CanRCONExec')){
                                    $servers_table .= "<form name='rcon-server'>
                                                        <div class='input-group col-lg-12 col-sm-12 col-xs-12'>
                                                          <span class='input-group-addon' id='sizing-addon2'>RCON Command: <span></span></span>
                                                          <input name='rcon-command' type='text' class='form-control' placeholder='Example: ulx ban <player> <time> \"<reason>\"' style='width:75%' aria-describedby='sizing-addon2' style='width:90%'>
                                                          <input name='server-rcon-id' type='hidden' value='". $server['ID'] ."'>
                                                          <input type='submit' value='Send Command' name='rcon-submit' class='btn btn-default'> 
                                                          <a href='#' name='server-restart-link'><button name='server-restart' data-toggle='popover' data-content='Restart this Server' class='btn btn-default pull-right'><span class='glyphicon glyphicon-off'></span></button></a>
                                                        </div>
                                                        </form>";
                                }

                                $servers_table .= "<hr>";
                                echo $servers_table;

                            }else{
                                ?>  
                                  <div class='alert alert-warning'>This server is offline or restarting.</div>      
                                <?php
                            }
                        }
                    }catch(Exception $e){
                        echo($e->getMessage());
                    }
                ?>
                </div>
            </div>
        </div>
    </div>


    <script src="js/jquery.js"></script>
    <script src='js/bootstrap.min.js'></script>
    <script>
        $(function() {
            $("#kick-result").hide();

            $('.dropdown-toggle').dropdown();
             
            $('.dropdown input, .dropdown label').click(function(e){
                e.stopPropagation(); // Fix input on dropdown menu
            });

            $(".glyphicon-refresh").click(function(){
                $("div[name='update']").load("administrate.php #update");

                $.ajax({
                    type: 'POST',
                    url: 'administrate.php',
                    data: null,
                    success: function(response){
                        $("body").html(response);
                    }
                })
            })

            $('[data-toggle="popover"]').popover({
                trigger: 'hover',
                'placement': 'left'
            });

            $(".form-group form#kickplayer").submit(function(e){
                e.preventDefault() // stop form from submitting
                var player = $(this).find("input[name='kick-player']").val();
                var reason = $(this).find("input[name='kick-reason']").val();
                var server = $(this).find("input[name='kickplayer-server-identifier']").val();
                var RCONHash = $(this).find("input[name='kickplayer-rcon-hash']").val();
               
                $.ajax({
                    type: 'POST',
                    url: ' php/ajax.php',
                    data: {
                        kick: 'true', 
                        kick_player_playername: player,
                        kick_player_reason: reason,
                        kickplayer_server: server,
                        RCONHash: RCONHash
                    },
                    success: function(response){
                        $("td:contains("+player+")").parent("tr").fadeOut(500, function(){
                            $(this).remove()
                        })
                        $("#kick-result").html(response);
                        $("#kick-result").fadeIn(500).delay(5000).fadeOut(500)
                    }
                })
            })


            $(".form-group form#banplayer").submit(function(e){
                e.preventDefault() // stop form from submitting
                var player = $(this).find("input[name='ban-player']").val();
                var reason = $(this).find("input[name='ban-reason']").val();
                var time = $(this).find("input[name='ban-time']").val();
                var time_t = $(this).find("select[name='ban-time-t']").val();
                var server = $(this).find("input[name='banplayer-server-identifier']").val();
                var RCONHash = $(this).find("input[name='banplayer-rcon-hash']").val();
               
                $.ajax({
                    type: 'POST',
                    url: 'php/ajax.php',
                    data: {
                        ban: 'true', 
                        ban_player_playername: player,
                        ban_player_reason: reason,
                        ban_time: time,
                        ban_time_t: time_t,
                        RCONHash: RCONHash,
                        banplayer_server: server
                    },
                    success: function(response){
                        $("td:contains("+player+")").parent("tr").fadeOut(500, function(){
                            $(this).remove()
                        })
                        $("#kick-result").html(response);
                        $("#kick-result").fadeIn(500).delay(5000).fadeOut(500)
                    }
                })
            })

            $("#self-update-form").submit(function(e){
                e.preventDefault();
                var selfUsername = $(this).find("input[name='self-username']").val();
                var selfContact = $(this).find("input[name='self-contact']").val();
                var selfPassword = $(this).find("input[name='self-password']").val();
                var selfNewPassword = $(this).find("input[name='self-new-password']").val();
                var shouldContact = $(this).find("select[name='self-new-shouldcontact']").val();

                $.ajax({
                    type: 'POST',
                    url: 'php/ajax.php',
                    data: {
                        update_self: 'true',
                        self_username: selfUsername,
                        self_contact: selfContact,
                        self_password: selfPassword,
                        self_newPassword: selfNewPassword,
                        self_shouldContact: shouldContact
                    },
                    success: function(response){
                        $("#selfupdate-result").html(response).hide().fadeIn(400);
                    }
                })
            })

           $("form[name='rcon-server']").submit(function(e){
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'php/ajax.php',
                    data: {
                        RCONCommand: $(this).find("input[name='rcon-command']").val(),
                        ServerID: $(this).find("input[name='server-rcon-id']").val(),
                        RCONSubmit: 'true'
                    },
                    success: function(response){
                        $("#kick-result").html(response);
                        $("#kick-result").fadeIn(500).delay(5000).fadeOut(500);
                    }
                })
           })  

           $("a[name='server-restart-link']").click(function(e){
                e.preventDefault();
                var ServerID = $(this).parents("form[name='rcon-server']").find("input[name='server-rcon-id']").val();

                $.ajax({
                    type: 'POST',
                    url: 'php/ajax.php',
                    data: {Server: ServerID, Restart: 'true'},
                    success: function(response){
                        $("#kick-result").html(response);
                        $("#kick-result").fadeIn(500).delay(4000).fadeOut(500)
                    }
                })
           })

           
            $("form[name='admin-chat-form']").submit(function(e){
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: 'php/ajax.php',
                    data: {message: $(this).find("input[name='admin-chat-message']").val()},
                    success: function(response){
                        $("#chat-response").html(response).hide().fadeIn(500).delay(3000).fadeOut(500);
                        $("div[name='chat-reload']").load("index.php #chat-reload");
                        $(this).find("input[name='admin-chat-message']").val("")
                    }
                })
            })

            $(document).on("click", "a[name='open-chat']", function(){
                $("div[name='chat-reload']").load("index.php #chat-reload");
            })

            $(document).on("click", "a[name='send-chat-to-server']", function(e){
                e.preventDefault();

                var chatMessage = $(this).parents("form[name='server-chat-form']").find("input[name='server-chat-message']").val();
                var serverID = $(this).attr("data-id");

                $.ajax({
                    type: 'POST',
                    url: 'php/ajax.php',
                    data: {
                        sendServerChat: 'true',
                        ChatMessage: chatMessage,
                        ToServer: serverID
                    },
                    success: function(response){
                        $("#server-chat-response").html(response).hide().fadeIn(400).delay(3000).fadeOut(400);
                        
                        $.ajax({
                            type: 'POST',
                            url: 'php/ajax.php',
                            data: {getAllChat: 'true'},
                            success: function(response){
                                $("div[name='serverchat-reload']").html(response).hide().fadeIn(400);
                            }
                        })

                        $("input[name='server-chat-message']").val("");
                    }
                })
            })

            $("form[name='server-chat-form']").submit(function(e){
                e.preventDefault();
            })

            $.ajax({
                type: 'POST',
                url: 'php/ajax.php',
                data: {getAllChat: 'true'},
                success: function(response){
                    $("div[name='serverchat-reload']").html(response);
                }
            })

            $("select[name='show-chat-from-server']").change(function(e){
                e.preventDefault();

                if ($(this).val() == "All"){
                    $.ajax({
                        type: 'POST',
                        url: 'php/ajax.php',
                        data: {getAllChat: 'true'},
                        success: function(response){
                            $("div[name='serverchat-reload']").html(response).hide().fadeIn(400);
                        }
                    })
                }else{
                    $.ajax({
                        type: 'POST',
                        url: 'php/ajax.php',
                        data: {chatServer: $(this).val()},
                        success: function(response){
                            $("div[name='serverchat-reload']").html(response).hide().fadeIn(400);
                        }
                    })
                }
            })
        })
    </script>
</body>
</html>

<?php
    $connection = null;
    exit();
    }
    else{
        header("Location: login.php");
    }
?>