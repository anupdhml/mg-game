<div>
    <!-- The bounding-box around the text input and the button -->
    <input type="hidden" id="game_assets_uri" value="<?php echo GamesModule::getAssetsUrl() . '/pyramid/'; ?>" />
    <div id="header" class="group">
        <a href="#menu-left" class="header_mm_left">
            <span class="words hidden top_btn">Words</span>
            <span class="back hidden"><img src="<?php echo GamesModule::getAssetsUrl(); ?>/oneup/images/back.png" /></span>
        </a>
        <a href="#menu-right" class="right setting"></a>
    </div>
    <div class="content" id="content">

        <div id="game_screen">
            <div class="wrap group">
                <header>
                    <div class="center" align="center">
                        <!-- The bounding-box around the text input and the button -->
               <span id="input_area">
                    <form action="javascript: return false;">
                        <!-- user text field -->
                        <input type="text" name="word" id="word" autocapitalize="off" autocorrect="off" autocomplete="off" class="level_1" placeholder="Enter a 4 letter word"/>
                        <a href="#" id="button-play" class="ir hidden"></a>
                    </form>
                </span>
                        <span id="countdown" class="countdown_amount"></span>
                    </div>
                </header>

                <div id="gamearea" class="group">
                    <div id="pass" class="level_1">Pass!</div>
                    <div id="no_js">Unfortunately we can't show the game as it relies on JavaScript which appears to be disabled on your
                        browser.
                    </div>
                    <!-- Images from the database appear here -->
                    <div id="stage">
                        <div id="holder">
                            <div id="image_container"></div>
                        </div>
                    </div>
                    <div id="new_image" class="level_1">Quit</div>
                </div>
                <div id="fieldholder" class="group">
                </div>

            </div>

            <footer class="group footer_level_1">
                <div>
                    What's in this image?
                </div>
            </footer>
        </div>

        <div id="how_to" class="hidden">
            <h2>HOW TO PLAY</h2>
            <div class="padding">
                This is just a custom text that need to be verified.
            </div>
        </div>

        <div id="learn_more" class="hidden">
            <h2>Learn More</h2>
            <div class="padding">One Up is part of a suite of games from the
                Metadata Games project. Metadata Games is a
                Free and Open Source (FOSS) online game
                system for gathering useful data on photo,
                audio, and moving image artifacts. By playing,
                you have a direct impact on the preservation
                and accessibility of vital cultural heritage
                collections for future generations.</div>
            <div class="padding">
                Metadata Games is created by the Tiltfactor
                Laboratory at Dartmouth College, with support
                from the National Endowment for the
                Humanities (NEH) and the American Council of
                Learned Societies (ACLS).
            </div>
            <h3 class="padding">METADATAGAMES</h3>
            <div>
                <span class="tiltfactor_logo" />
                <span class="dartmouth_logo" />
                <span class="neh_logo" />
            </div>

        </div>

        <div id="login" class="text-center index_screen hidden">
            <div class="logo"><img src="<?php echo GamesModule::getAssetsUrl(); ?>/pyramid/images/splash_logo.png" /></div>
            <div class="button">
                <input class="input" type="text" autocapitalize="off" autocorrect="off" autocomplete="off" id="username" name="username" placeholder="Username" tabindex="1" />
            </div>
            <div class="button">
                <input class="input" type="password" autocapitalize="off" autocorrect="off" autocomplete="off" id="password" name="password" placeholder="Password" tabindex="2" />
            </div>
            <div class="button">
                <input id="rememberMe" type="checkbox" value="1" name="rememberMe" style="width:20px; height: 20px;" tabindex="3">
                <label for="rememberMe">Remember me next time</label>
            </div>
            <div class="button">
                <a href="#" id="btn_login" class="button login"><span>LOGIN</span></a>
            </div>
            <div class="button">
                or
            </div>
            <div class="button">
                <a href="#" location="register" class="button new_user"><span>GET A USERNAME</span></a>
                <span class="hidden"><a href=""></a></span>
            </div>
            <div id="facebook"><img src="<?php echo GamesModule::getAssetsUrl(); ?>/pyramid/images/facebook.png" /></div>
        </div>

        <div id="register" class="text-center index_screen hidden">
            <div class="logo"><img src="<?php echo GamesModule::getAssetsUrl(); ?>/pyramid/images/splash_logo.png" /></div>
            <div class="button">
                <input class="input" type="text" autocapitalize="off" autocorrect="off" autocomplete="off" id="username" name="username" placeholder="Choose Username" value="" tabindex="1" />
            </div>
            <div class="button">
                <input class="input" type="password" autocapitalize="off" autocorrect="off" autocomplete="off" id="password" name="password" placeholder="Choose Password" value="" tabindex="2" />
            </div>
            <div class="button">
                <input class="input" type="password" autocapitalize="off" autocorrect="off" autocomplete="off" id="verifyPassword" name="verifyPassword" placeholder="Verify Password" value="" tabindex="3" />
            </div>
            <div class="button">
                <input class="input" type="email" autocapitalize="off" autocorrect="off" autocomplete="off" id="email" name="email" placeholder="Choose E-mail" tabindex="4" value="" />
            </div>
            <div class="button">
                <a href="#" id="btn_register" class="button login"><span>START PLAYING</span></a>
            </div>
        </div>
    </div>
    <nav id="menu-left">
    </nav>
    <nav id="menu-right" style="visibility: hidden;">
        <ul>
            <li class="back_blue row"><div><a href="<?php echo Yii::app()->baseUrl; ?>/index.php/games/Pyramid/" location="main_screen"><span>HOME</span></a></div></li>
            <li class="back_blue row" id="mmenuPlay"><div><a href="#" location="game_screen"><span>PLAY</span></a></div></li>
            <li class="back_blue row touch" id="mmenuLogin"><div><a  href="#" location="login"><span>LOGIN</span></a></div></li>
            <li class="back_blue row"><div><a href="#" location="how_to"><span>HOW TO PLAY</span></a></div></li>
            <li class="back_blue row"><div><a href="#" location="learn_more"><span>LEARN MORE</span></a></div></li>
            <li class="back_blue row hidden touch" id="mmenuLogout"><div><a href="#" location="logout"><span>LOGOUT</span></a></div></li>
        </ul>
    </nav>
</div>


<div style="height: 0px; padding: 0; margin: 0; overflow:scroll;">

    <script id="template-licence" type="text/x-jquery-tmpl">
        <h4>${name}</h4>

        <p>${description}</p>
    </script>

    <script id="template-make-sound" type="text/x-jquery-tmpl">
        <audio id='make_sound' style="height: 0px;">
            <source src="${ogg_path}" type="audio/ogg">
            <source src="${mp3_path}" type="audio/mpeg">
        </audio>
    </script>
    <script id="template-turn" type="text/x-jquery-tmpl">
        <div style="text-align:center">
            <img src="${url}" alt="game image" id="image_to_tag" style="width: auto !important; height: auto !important; "/>
        </div>
    </script>
    <script id="template-pyramid-step" type="text/x-jquery-tmpl">
        <div style="margin:5px auto;background-color:#EEEEEE;border:1px solid #CCCCCC;width:${width}px;">${tag}</div>
    </script>
    <script id="template-final-summary-play-once" type="text/x-jquery-tmpl">
        <div style="text-align:center" class="group">
            <a href="${url_full_size}" rel="zoom" title="${licence_info}"><img src="${url}" alt="game image"/></a>
        </div>
    </script>
    <script id="template-more-info" type="text/x-jquery-tmpl">
        <a href="${url}">Click here to learn more about ${name}</a>
    </script>
    <script id="template-final-info" type="text/x-jquery-tmpl">
        <div class="final">${finalMsg}<br />${finalMsg_2ndline}</div>
        <div class="level_9 pyramid">&nbsp;</div>
        <div class="level_8 pyramid">&nbsp;</div>
        <div class="level_7 pyramid">&nbsp;</div>
        <div class="level_6 pyramid">&nbsp;</div>
        <div class="level_5 pyramid">&nbsp;</div>
        <div class="level_4 pyramid">&nbsp;</div>
        <div class="level_3 pyramid">&nbsp;</div>
        <div class="level_2 pyramid">&nbsp;</div>
        <div class="level_1 pyramid"></div>
        <div class="new_game"><a href="#" id="button-play-again"><span>&nbsp;</span></a></div>
    </script>
    <script id="template-final-info-play-once" type="text/x-jquery-tmpl">
        You'll be redirected in <span id="remainingTime">${remainingTime}</span> seconds. <a
            href="${play_once_and_move_on_url}">Click here to proceed right away.</a></p>
    </script>
    <script id="template-info-modal-critical-error" type="text/x-jquery-tmpl">
        ${error} <p>Return to the <a href="${arcade_url}">arcade</a>.</p>
    </script>
</div>