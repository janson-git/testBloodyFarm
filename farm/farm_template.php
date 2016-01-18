<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="30">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title></title>
    
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
        select.yard {
            width: 200px;
            height: 100px;
        }
        .control {
            width: 600px;
        }
        #commandForm {
            border: 1px solid #999;
            background-color: #eef;
            padding: 10px 15px;
            margin-top: 10px;
        }
        #commandForm input {
            width: 310px;
        }
        #commandForm input[type=submit] {
            width: 200px;
            float: right;
        }
        .clean {
            clear: both;
        }
        #commandHelp {
            border: 1px dashed #333;
            background-color: #efffef;
            font-size: 11pt;
            margin: 5px;
            padding: 5px 10px;
        }
        #commandHelp pre {
            font-size: 10pt;
            margin: 5px 10px;
            padding: 5px 10px;
        }
    </style>
    
    <script src="/farm/jquery.js"></script>
</head>
<body>

<table>
    <tr>
        <td>
            <label for="yard_1">[[YARD]] 1</label>
            <br />
            <select id="yard_1" name="yard_1" class="yard" multiple>
                <?php 
                    foreach ($yards[1] as $id) {
                        echo "<option name=\"sheep[]\" value=\"{$id}\">[[SHEEP]]{$id}</option>";
                    }
                ?>
            </select>
        </td>
        <td>
            <label for="yard_2">[[YARD]] 2</label>
            <br />
            <select id="yard_2" name="yard_2" class="yard" multiple>
                <?php
                foreach ($yards[2] as $id) {
                    echo "<option name=\"sheep[]\" value=\"{$id}\">[[SHEEP]]{$id}</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <label for="yard_3">[[YARD]] 3</label>
            <br />
            <select id="yard_3" name="yard_3" class="yard" multiple>
                <?php
                foreach ($yards[3] as $id) {
                    echo "<option name=\"sheep[]\" value=\"{$id}\">[[SHEEP]]{$id}</option>";
                }
                ?>
            </select>
        </td>
        <td>
            <label for="yard_4">[[YARD]] 4</label>
            <br />
            <select id="yard_4" name="yard_4" class="yard" multiple>
                <?php
                foreach ($yards[4] as $id) {
                    echo "<option name=\"sheep[]\" value=\"{$id}\">[[SHEEP]]{$id}</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2">[[BLOODY_INDEX]]: <?php echo $bloodyIndex; ?></td>
    </tr>
</table>

<div class="control">
    <form name="refreshPageForm" action="" method="post">
        <input type="submit" id="refresh" value="[[REFRESH]]">
    </form>

    <br />
    <input class="deleteButton" value="[[KILL_SELECTED]]" type="button">

    <br />
    <input class="moveButton" type="button" value="[[MOVE_TO_YARD]] 1" data-value="1">
    <input class="moveButton" type="button" value="[[MOVE_TO_YARD]] 2" data-value="2">
    <input class="moveButton" type="button" value="[[MOVE_TO_YARD]] 3" data-value="3">
    <input class="moveButton" type="button" value="[[MOVE_TO_YARD]] 4" data-value="4">
    
    
    <form action="" method="post" name="moveSheepsForm" id="moveSheepsForm">
        <input type="hidden" id="moveToYard" name="move[yard]" value="">
    </form>

    <form action="" method="post" name="killSheepsForm" id="killSheepsForm"></form>

    
    <form action="" method="post" name="commandForm" id="commandForm">
        <label for="command">[[COMMAND]]</label>
        <br />
        <input type="text" id="command" name="command" value="">
        <input type="submit" name="submit" value="[[EXECUTE]]" />
        <div class="clean"></div>
        <div id="commandHelp">[[COMMAND_HELP]]</div>
    </form>
</div>

<script>
    $(function() {
        $(".moveButton").click(function() {
            var yardId = $(this).data('value');
            $("#moveToYard").val( parseInt(yardId));
            
            $(".yard option:selected").each(function() {
                $("#moveSheepsForm").append(
                    "<input type=\"hidden\" name=\"move[sheeps][]\" value=\"" + parseInt($(this).attr('value')) + "\">")
            });
            
            $("#moveSheepsForm").submit();
        });
        
        $(".deleteButton").click(function() {
            $(".yard option:selected").each(function() {
                $("#killSheepsForm").append(
                    "<input type=\"hidden\" name=\"kill[sheeps][]\" value=\"" + parseInt($(this).attr('value')) + "\">")
            });

            $("#killSheepsForm").submit();
        });
        
        
        $("#refresh").click(function() {
            $.ajax($("#moveSheepsForm").attr('action'), {
                dataType: "json",
                success: function(data) {
                    var i,yard, id,sheepId;
                    for (i = 1; i <= 4; i++) {
                        yard = $("#yard_" + i);
                        $(yard).html('');
                        if (data[i] !== undefined) {
                            for (id in data[i]) {
                                sheepId = data[i][id];
                                $(yard).append(
                                    "<option name=\"sheep[]\" value=\"" + sheepId + "\">[[SHEEP]]" + sheepId + "</option>"
                                );
                            }
                        }
                    }
                }
            });
            return false;
        });
    });
    
</script>
</body>
</html>