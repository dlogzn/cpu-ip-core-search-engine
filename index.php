<html>
    <head>
        <title>CPU IP CORE SEARCH ENGINE</title>
        <style>
            label {
                font-size: 14px;
                color: #CC2727;
                font-weight: bold;
            }

            .form_select {
                width: 100%;
                padding: 10px 5px;
                border-color: #d6d6d6;
            }
            .form_input {
                width: 97%;
                padding: 10px 5px;
                border: 1px solid #d6d6d6;
            }
            .validation_error {
                height: 12px; font-size: 12px; color: red; margin-top: 5px;
            }
        </style>
        <style>
            #ajax_loading{
                position: fixed;
                top: 0;
                width: 100%;
                height:100%;
                display: none;
                background: rgba(0,0,0,0.6);
            }
            .ajax_loading_spinner {
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .loading_spinner {
                width: 40px;
                height: 40px;
                border: 4px #ddd solid;
                border-top: 4px #2e93e6 solid;
                border-radius: 50%;
                animation: sp-anime 0.8s infinite linear;
            }
            @keyframes sp-anime {
                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css">
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
        <script type="text/javascript">
            $(document).ajaxStart(function() {
                let zIndex = 100;
                if ($('body').hasClass('modal-open')) {
                    zIndex = parseInt($('div.modal').css('z-index')) + 1;
                }
                $("#ajax_loading").css({
                    'z-index': zIndex
                });
                $("#ajax_loading").fadeIn(0);
            });

            $(document).ajaxStop(function () {
                $("#ajax_loading").fadeOut(300);
            });
        </script>
    </head>
    <body>
        <form id="cpu_ip_core_search_engine_form">
            <h3>CPU Details</h3>
            <br>
            <div style="display: flex; width: 70%;">
                <div style="flex: auto; width: 25%; padding-top: 10px;">
                    <label>Performance</label>
                </div>
                <div style="flex: auto; width: 75%;">
                    <select name="performance" id="performance" class="form_select">
                        <option value="">Select Performance</option>
                        <option value="Low">Low</option>
                        <option value="High">High</option>
                    </select>
                    <div class="validation_error"></div>
                </div>
            </div>
            <br>
            <div style="display: flex; width: 70%;">
                <div style="flex: auto; width: 25%; padding-top: 10px;">
                    <label>Number of Bits</label>
                </div>
                <div style="flex: auto; width: 75%;">
                    <select name="number_of_bits" id="number_of_bits" class="form_select">
                        <option value="">Select Bit</option>
                        <option value="8">8</option>
                        <option value="16">16</option>
                        <option value="32">32</option>
                        <option value="64">64</option>
                    </select>
                    <div class="validation_error"></div>
                </div>
            </div>
            <br>
            <div style="display: flex; width: 70%;">
                <div style="flex: auto; width: 25%; padding-top: 10px;">
                    <label>ISA Type</label>
                </div>
                <div style="flex: auto; width: 75%;">
                    <select name="isa_type" id="isa_type" class="form_select">
                        <option value="">Select ISA TYPE</option>
                        <option value="RISC-V">RISC-V</option>
                        <option value="RISC">RISC</option>
                        <option value="ARM">ARM</option>
                        <option value="All">I don't care</option>
                    </select>
                    <div class="validation_error"></div>
                </div>
            </div>
            <br>
            <div style="display: flex; width: 70%;">
                <div style="flex: auto; width: 25%; padding-top: 10px;">
                    <label>Keyword</label>
                </div>
                <div style="flex: auto; width: 75%;">
                    <input name="keyword" id="keyword" class="form_input">
                    <div class="validation_error"></div>
                </div>
            </div>
            <br>
            <h3>Your Details</h3>
            <br>
            <div style="display: flex; width: 70%;">
                <div style="flex: auto; width: 25%; padding-top: 10px;">
                    <label>Email</label>
                </div>
                <div style="flex: auto; width: 75%;">
                    <input autocomplete="off" name="email" id="email" class="form_input">
                    <div class="validation_error"></div>
                </div>
            </div>
            <br><br>
            <div>
                <label for="get_price_quote">
                    <input type="checkbox" id="get_price_quote" checked>Yes, I would also like to get price quote from CPU vendors
                </label>
            </div>
            <br>
            <div style="margin-top: 10px;">
                <button type="submit" style="padding: 8px 35px; background-color: #cd2727; border: solid 1px #b11a1a; color: white; background-image: linear-gradient(to bottom, rgba(0, 0, 0, .1), rgba(255, 255, 255, .1));">Send</button>
            </div>
            <br>
        </form>


        <div id="ajax_loading">
            <div class="ajax_loading_spinner">
                <span class="loading_spinner"></span>
            </div>
        </div>


        <script type="text/javascript">
            $(document).on('submit', '#cpu_ip_core_search_engine_form', function (event) {
                event.preventDefault();
                $('.validation_error').empty();
                let formData = new FormData(this);
                if ($('#get_price_quote').is(':checked')) {
                    formData.append('get_price_quote', 1);
                } else {
                    formData.append('get_price_quote', 0);
                }
                $.ajax({
                    method: 'post',
                    url: 'search.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (result) {
                        console.log(result);
                        let response = JSON.parse(result);
                        console.log(response);
                        if (parseInt(response.success) === 1) {
                            $.toast({
                                heading: 'Success',
                                text : response.message,
                                showHideTransition : 'slide',
                                icon: 'success',
                                hideAfter: 10000,
                                position : 'bottom-center'
                            });
                        } else {
                            if (response.hasOwnProperty('messages')) {
                                $.each(response.messages, function (key, value) {
                                    $('#' + key).parent().find('.validation_error').text(value);
                                });
                            } else {
                                $.toast({
                                    heading: 'Error',
                                    text : response.message,
                                    showHideTransition : 'slide',
                                    icon: 'error',
                                    hideAfter: 5000,
                                    position : 'bottom-center'
                                });
                            }
                        }

                    },
                    error: function (xhr) {
                        console.log(xhr);
                    }
                });
            });
        </script>
    </body>
</html>
















