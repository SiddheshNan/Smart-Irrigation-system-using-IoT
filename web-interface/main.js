 $(document).ready(function () {
            var outObj = {};
            var chk = false;
            // Creating WS ---------
            console.log("Creating WS Connection");
            try {
                var ws = new WebSocket("wss://api.thinger.io/v2/users/<username>/devices/<device>?authorization=<JWT>");
                console.log('WS Connection Created');
            }
            catch (e) {
                console.error("Error Creating WS Connection");
                console.error(e);
            }
            //---------------------
            // Send data ---------
            console.log('Sending data to WS...');
            try {
                ws.onopen = function (event) {
                    ws.send('{"resource":"Device1","interval":1,"enabled":true}');
                    ws.send('{"resource":"temp","interval":1,"enabled":true}');
                    ws.send('{"resource":"moist","interval":1,"enabled":true}');
                    ws.send('{"resource":"hum","interval":1,"enabled":true}');
                    console.log('Data Sent to WS');
                }
            } catch (e) {
                console.error("Error Sending data to WS");
                console.error(e);
            }
            //---------------------
            // Reciv data ---------
            console.log('Reciving Data');
            try {
                var logged = false;
                ws.onmessage = function (event) {
                    if (!logged) {
                        console.log("reciving onMessage data..")
                        logged = true;
                    }
                    if (chk == true) {
                        document.getElementById('userOutput').innerHTML = '<div class="isa_success"><i class="fas fa-check"></i>Connected to the Server! Please turn ON / OFF the Motor.</div>';
                        chk = false;
                    }
                    var recv = JSON.parse(event.data);
                    if (recv.resource == 'temp') {
                        outObj.temp = recv.out;
                        document.getElementById('tempData').innerHTML = '<span class="badge badge-success">' + outObj.temp + " °C</span>";
                    }
                    if (recv.resource == 'Device1') {
                        if (recv.in == true) {
                            outObj.motor = "OFF"
                        }
                        if (recv.in == false) {
                            outObj.motor = "ON"
                        }
                        document.getElementById('motorData').innerHTML = '<span class="badge badge-success">' + outObj.motor + "</span>";
                    }
                    if (recv.resource == 'moist') {
                        outObj.moist = recv.out;
                        document.getElementById('moistData').innerHTML = '<span class="badge badge-success">' + outObj.moist + "</span>";
                    }
                    if (recv.resource == 'hum') {
                        outObj.hum = recv.out;
                        document.getElementById('humData').innerHTML = '<span class="badge badge-success">' + outObj.hum + " %</span>";
                    }
                    console.log(outObj);
                }
            }
            catch (e) {
                console.error("error onMessage data..")
                console.error(e);
            }
            //---------------------
            // Exp
            if (ws.readyState === WebSocket.CONNECTING) {
                var connectingBadge = '<span class="badge badge-primary">Connecting..</span>';
                document.getElementById('moistData').innerHTML = connectingBadge;
                document.getElementById('motorData').innerHTML = connectingBadge;
                document.getElementById('tempData').innerHTML = connectingBadge;
                document.getElementById('humData').innerHTML = connectingBadge;
            }
            ws.onerror = function (event) {
                console.error('Web Socket Error');
                console.error(event);
                var failedBadge = '<span class="badge badge-danger">Failed</span>';
                document.getElementById('moistData').innerHTML = failedBadge;
                document.getElementById('motorData').innerHTML = failedBadge;
                document.getElementById('tempData').innerHTML = failedBadge;
                document.getElementById('humData').innerHTML = failedBadge;
                document.getElementById('userOutput').innerHTML = '<div class="isa_error"><i class="fas fa-times-circle"></i>Failed to connect to the Server!</div>';
                $('#exampleModal').modal('show');
            };
            $('#on_button').click(function (e) {
                e.preventDefault();
                console.log("ON_Button_Pressed");
                if ((ws.readyState === WebSocket.OPEN) && (logged == true)) {
                    ws.send('{"resource":"Device1","in":false}');
                    document.getElementById('userOutput').innerHTML = '<div class="isa_success"><i class="fas fa-check-circle"></i>Motor Turned <b>ON</b></div>';
                }
                else if (ws.readyState === WebSocket.CLOSED) {
                    document.getElementById('userOutput').innerHTML = '<div class="isa_error"><i class="fas fa-times-circle"></i>Failed to Turn <b>ON</b> Motor!</div>';
                    $('#exampleModal').modal('show');
                }
                else {
                    document.getElementById('userOutput').innerHTML = '<div class="isa_info"><i class="fas fa-undo-alt"></i>Please wait while connecting to the server..</div>';
                    chk = true;
                }
            });
            $('#off_button').click(function (e) {
                e.preventDefault();
                console.log("OFF_Button_Pressed");
                if ((ws.readyState === WebSocket.OPEN) && logged == true) {
                    ws.send('{"resource":"Device1","in":true}');
                    document.getElementById('userOutput').innerHTML = '<div class="isa_success"><i class="fas fa-check-circle"></i>Motor Turned <b>OFF</b></div>';
                }
                else if (ws.readyState === WebSocket.CLOSED) {
                    document.getElementById('userOutput').innerHTML = '<div class="isa_error"><i class="fas fa-times-circle"></i>Failed to Turn <b>OFF</b> Motor!</div>';
                    $('#exampleModal').modal('show');
                }
                else {
                    document.getElementById('userOutput').innerHTML = '<div class="isa_info"><i class="fas fa-undo-alt"></i>Please wait while connecting to the server..</div>';
                    chk = true;
                }
            });
        });
