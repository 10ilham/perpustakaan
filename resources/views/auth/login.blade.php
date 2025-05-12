<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan MTSN 6 GARUT</title>
    <link href="{{ asset('assets/img/logo_mts.png') }}" rel="icon" type="image/x-icon">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">

    <!-- Box Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        :root {
            --primary-color: #4EA685;
            --secondary-color: #57B894;
            --green-light: #98eb24;
            --black: #000000;
            --white: #ffffff;
            --gray: #efefef;
            --gray-2: #757575;
            --cyan-light: #03e9f4;

            /* Panda colors */
            --panda-black: #000;
            --panda-white: #fff;
            --panda-gray: #222;

            --facebook-color: #4267B2;
            --google-color: #DB4437;
            --twitter-color: #1DA1F2;
            --insta-color: #E1306C;
        }

        /* Loading screen styles */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }

        .spinner-container {
            position: relative;
            width: 150px;
            height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-panda {
            position: relative;
            width: 120px;
            height: 120px;
            animation: float 2s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .loading-panda-face {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            position: relative;
            margin: 0 auto;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .loading-panda-ear {
            width: 24px;
            height: 24px;
            background: black;
            border-radius: 12px;
            position: absolute;
            top: 0;
        }

        .loading-panda-ear.left {
            left: 12px;
        }

        .loading-panda-ear.right {
            right: 12px;
        }

        .loading-panda-eye {
            width: 12px;
            height: 12px;
            background: black;
            position: absolute;
            border-radius: 50%;
            top: 30px;
        }

        .loading-panda-eye.left {
            left: 22px;
            animation: blinkEye 2.5s infinite;
        }

        .loading-panda-eye.right {
            right: 22px;
            animation: blinkEye 2.5s infinite;
        }

        @keyframes blinkEye {

            0%,
            100% {
                height: 12px;
            }

            15% {
                height: 2px;
            }

            20% {
                height: 12px;
            }
        }

        .loading-panda-nose {
            width: 16px;
            height: 10px;
            background: black;
            border-radius: 10px 10px 5px 5px;
            position: absolute;
            bottom: 22px;
            left: calc(50% - 8px);
        }

        .loading-spinner {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 4px solid transparent;
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            color: white;
            font-size: 18px;
            margin-top: 20px;
            font-weight: 500;
        }

        .loading-dots:after {
            content: '';
            animation: dots 1.5s steps(5, end) infinite;
        }

        @keyframes dots {

            0%,
            20% {
                content: '.';
            }

            40% {
                content: '..';
            }

            60% {
                content: '...';
            }

            80%,
            100% {
                content: '';
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Mobile responsiveness for loading screen */
        @media only screen and (max-width: 425px) {
            .spinner-container {
                width: 120px;
                height: 120px;
            }

            .loading-panda {
                width: 100px;
                height: 100px;
            }

            .loading-panda-face {
                width: 70px;
                height: 70px;
            }

            .loading-spinner {
                width: 120px;
                height: 120px;
            }

            .loading-text {
                font-size: 16px;
            }
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100vh;
            overflow: hidden;
        }

        .container {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            height: 100vh;
        }

        .col {
            width: 50%;
        }

        .align-items-center {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .form-wrapper {
            width: 100%;
            max-width: 28rem;
            position: relative;
        }

        .form {
            padding: 1rem;
            background-color: var(--white);
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1s;
            position: relative;
            z-index: 10;
            /* Increased z-index to ensure it's above panda */
            margin-top: 80px;
            /* Add space for panda face */
            transform-origin: center top;
            /* Form bergerak dari tengah atas */
        }

        /* Fixed animation when form covers panda eyes */
        .form.password-focus {
            transform: translateY(-80px) scale(1) !important;
            transition: all 0.4s ease-in-out !important;
        }

        /* Ensure correct display of forms during toggle */
        .container.sign-in .col.forgot-password .form {
            display: none;
        }

        .container.forgot-password .col.sign-in .form {
            display: none;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 1rem 0;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            font-size: 1.4rem;
            color: var(--gray-2);
        }

        .input-group input {
            width: 100%;
            padding: 1rem 3rem;
            font-size: 1rem;
            background-color: var(--gray);
            border-radius: .5rem;
            border: 0.125rem solid var(--white);
            outline: none;
        }

        .input-group input:focus {
            border: 0.125rem solid var(--primary-color);
        }

        .btn-animated {
            position: relative;
            display: inline-block;
            width: 100%;
            padding: 10px 20px;
            color: var(--primary-color);
            background: transparent;
            font-size: 16px;
            text-decoration: none;
            text-transform: uppercase;
            overflow: hidden;
            transition: .5s;
            margin-top: 15px;
            border: none;
            letter-spacing: 4px;
            cursor: pointer;
            text-align: center;
        }

        .btn-animated:hover {
            background: var(--primary-color);
            color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px var(--primary-color),
                0 0 15px var(--primary-color),
                0 0 50px var(--primary-color),
                0 0 0px var(--primary-color);
        }

        .btn-animated span {
            position: absolute;
            display: block;
        }

        .btn-animated span:nth-child(1) {
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--green-light));
            animation: btn-anim1 1s linear infinite;
        }

        @keyframes btn-anim1 {
            0% {
                left: -100%;
            }

            50%,
            100% {
                left: 100%;
            }
        }

        .btn-animated span:nth-child(2) {
            top: -100%;
            right: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(180deg, transparent, var(--green-light));
            animation: btn-anim2 1s linear infinite;
            animation-delay: .25s
        }

        @keyframes btn-anim2 {
            0% {
                top: -100%;
            }

            50%,
            100% {
                top: 100%;
            }
        }

        .btn-animated span:nth-child(3) {
            bottom: 0;
            right: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(270deg, transparent, var(--green-light));
            animation: btn-anim3 1s linear infinite;
            animation-delay: .5s
        }

        @keyframes btn-anim3 {
            0% {
                right: -100%;
            }

            50%,
            100% {
                right: 100%;
            }
        }

        .btn-animated span:nth-child(4) {
            bottom: -100%;
            left: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(360deg, transparent, var(--green-light));
            animation: btn-anim4 1s linear infinite;
            animation-delay: .75s
        }

        @keyframes btn-anim4 {
            0% {
                bottom: -100%;
            }

            50%,
            100% {
                bottom: 100%;
            }
        }

        .form p {
            margin: 1rem 0;
            font-size: .7rem;
        }

        .flex-col {
            flex-direction: column;
        }

        .pointer {
            cursor: pointer;
        }

        .container.sign-in .form.sign-in,
        .container.sign-in .social-list.sign-in,
        .container.sign-in .social-list.sign-in>div,
        .container.forgot-password .form.forgot-password,
        .container.forgot-password .social-list.forgot-password,
        .container.forgot-password .social-list.forgot-password>div {
            transform: scale(1);
        }

        .content-row {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 6;
            width: 100%;
        }

        .text {
            margin: 4rem;
            color: var(--white);
        }

        .text h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 2rem 0;
            transition: 1s ease-in-out;
        }

        .text p {
            font-weight: 600;
            transition: 1s ease-in-out;
            transition-delay: .2s;
        }

        .img img {
            width: 30vw;
            transition: 1s ease-in-out;
            transition-delay: .4s;
        }

        .text.sign-in h2,
        .text.sign-in p,
        .img.sign-in img {
            transform: translateX(-250%);
        }

        .text.forgot-password h2,
        .text.forgot-password p,
        .img.forgot-password img {
            transform: translateX(250%);
        }

        .container.sign-in .text.sign-in h2,
        .container.sign-in .text.sign-in p,
        .container.sign-in .img.sign-in img,
        .container.forgot-password .text.forgot-password h2,
        .container.forgot-password .text.forgot-password p,
        .container.forgot-password .img.forgot-password img {
            transform: translateX(0);
        }

        /* BACKGROUND */

        .container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            height: 100vh;
            width: 300vw;
            transform: translate(35%, 0);
            background-image: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: 1s ease-in-out;
            z-index: 6;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            border-bottom-right-radius: max(50vw, 50vh);
            border-top-left-radius: max(50vw, 50vh);
        }

        .container.sign-in::before {
            transform: translate(0, 0);
            right: 50%;
        }

        .container.forgot-password::before {
            transform: translate(100%, 0);
            right: 50%;
        }

        /* PANDA STYLING */
        .panda-container {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 5;
            /* Reduced z-index so the form appears on top */
            transition: all 1s ease-in-out;
        }

        .follow-form-left {
            left: 25% !important;
        }

        .follow-form-right {
            left: 75% !important;
        }

        .panda {
            position: relative;
            width: 200px;
            margin: 0 auto;
        }

        .face {
            width: 200px;
            height: 200px;
            background: #fff;
            border-radius: 100%;
            margin: 50px auto;
            box-shadow: 0 10px 15px rgba(0, 0, 0, .15);
            z-index: 50;
            position: relative;
        }

        /* Important: this is for the form element inside the panda, not the login form */
        .panda .form {
            position: relative;
            z-index: 2;
        }

        .hand {
            width: 40px;
            height: 30px;
            border-radius: 50px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, .15);
            background: #000;
            margin: 5px;
            position: absolute;
            top: 160px;
            z-index: 30;
            transition: .3s ease-in-out;
            transform-origin: bottom;
        }

        .hand:after,
        .hand:before {
            content: '';
            width: 40px;
            height: 30px;
            border-radius: 50px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, .15);
            background: #000;
            margin: 5px;
            position: absolute;
            left: -5px;
            top: 11px;
        }

        .hand:before {
            top: 26px;
        }

        .eye-shade {
            background: #000;
            width: 50px;
            height: 80px;
            margin: 10px;
            position: absolute;
            top: 35px;
            left: 25px;
            transform: rotate(220deg);
            border-radius: 25px / 20px 30px 35px 40px;
        }

        .eye-shade.rgt {
            transform: rotate(140deg);
            left: 105px;
        }

        .eye-white {
            position: absolute;
            width: 30px;
            height: 30px;
            border-radius: 100%;
            background: #fff;
            z-index: 500;
            left: 40px;
            top: 80px;
            overflow: hidden;
        }

        .eye-white.rgt {
            right: 40px;
            left: auto;
        }

        .eye-ball {
            position: absolute;
            width: 0px;
            height: 0px;
            left: 20px;
            top: 20px;
            max-width: 10px;
            max-height: 10px;
            transition: .1s;
        }

        .eye-ball:after {
            content: '';
            background: #000;
            position: absolute;
            border-radius: 100%;
            right: 0;
            bottom: 0px;
            width: 20px;
            height: 20px;
        }

        .nose {
            position: absolute;
            height: 20px;
            width: 35px;
            bottom: 40px;
            left: 0;
            right: 0;
            margin: auto;
            border-radius: 50px 20px / 30px 15px;
            transform: rotate(15deg);
            background: #000;
        }

        .body {
            background: #fff;
            position: absolute;
            top: 200px;
            left: -20px;
            border-radius: 100px 100px 100px 100px / 126px 126px 96px 96px;
            width: 250px;
            height: 282px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, .3);
        }

        .hand.rgt:after,
        .hand.rgt:before {
            left: auto;
            right: -5px;
        }

        #leftHand {
            left: -140px;
            transform: rotate(-30deg) translateX(-5px) translateY(60px);
            transition: all 0.4s ease-in-out;
        }

        #rightHand {
            right: -140px;
            transform: rotate(30deg) translateX(5px) translateY(60px);
            transition: all 0.4s ease-in-out;
        }

        /* These classes are now handled directly by JavaScript for better precision */
        .form.sign-in.up+.panda-container .hand,
        .form.sign-in.up~.panda-container .hand {
            /* Animation now controlled by JS */
        }

        .form.sign-in.up+.panda-container .hand.rgt,
        .form.sign-in.up~.panda-container .hand.rgt {
            /* Animation now controlled by JS */
        }

        /* Ukuran panda saat berada di form login */
        .container.sign-in .panda {
            transform: scale(1.2);
        }

        /* Ukuran panda saat berada di form lupa password */
        .container.forgot-password .panda {
            transform: scale(1);
        }

        .foot {
            top: 360px;
            left: -80px;
            position: absolute;
            background: #000;
            z-index: 1400;
            box-shadow: 0 5px 5px rgba(0, 0, 0, .2);
            border-radius: 40px 40px 39px 40px / 26px 26px 63px 63px;
            width: 82px;
            height: 120px;
        }

        .foot:after {
            content: '';
            width: 55px;
            height: 65px;
            background: #222;
            border-radius: 100%;
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            margin: auto;
        }

        .finger {
            position: absolute;
            width: 25px;
            height: 35px;
            background: #222;
            border-radius: 100%;
            top: 10px;
            right: 5px;
        }

        .finger:after,
        .finger:before {
            content: '';
            position: absolute;
            width: 20px;
            height: 35px;
            background: #222;
            border-radius: 100%;
            top: 0;
            right: 30px;
        }

        .finger:before {
            right: 55px;
            top: 5px;
        }

        .foot.rgt {
            left: auto;
            right: -80px;
        }

        .foot.rgt .finger {
            left: 5px;
            right: auto;
        }

        .foot.rgt .finger:after {
            left: 30px;
            right: auto;
        }

        .foot.rgt .finger:before {
            left: 55px;
            right: auto;
        }

        /* Error animation */
        .form.sign-in.wrong-entry {
            animation: wrong-log 0.3s;
        }

        @keyframes eye-blink {
            to {
                height: 30px;
            }
        }

        @keyframes wrong-log {

            0%,
            100% {
                left: 0px;
            }

            20%,
            60% {
                left: 20px;
            }

            40%,
            80% {
                left: -20px;
            }
        }

        /* RESPONSIVE */
        @media only screen and (max-width: 425px) {

            .container::before,
            .container.sign-in::before,
            .container.forgot-password::before {
                height: 100vh;
                border-bottom-right-radius: 0;
                border-top-left-radius: 0;
                z-index: 0;
                transform: none;
                right: 0;
            }

            .container.sign-in .col.sign-in,
            .container.forgot-password .col.forgot-password {
                transform: translateY(0);
            }

            .content-row {
                align-items: flex-start !important;
            }

            .content-row .col {
                transform: translateY(0);
                background-color: unset;
            }

            .col {
                width: 100%;
                position: absolute;
                padding: 2rem;
                background-color: var(--white);
                border-top-left-radius: 2rem;
                border-top-right-radius: 2rem;
                transform: translateY(100%);
                transition: 1s ease-in-out;
            }

            .row {
                align-items: flex-end;
                justify-content: flex-end;
            }

            .form,
            .social-list {
                box-shadow: none;
                margin: 0;
                padding: 0;
                margin-top: 180px;
                /* Add more space for panda on mobile */
            }

            .text {
                margin: 0;
            }

            .text p {
                display: none;
            }

            .text h2 {
                margin: .5rem;
                font-size: 2rem;
            }

            .panda-container {
                top: 100px !important;
                transform: translate(-50%, 0) !important;
                z-index: 5;
            }

            .follow-form-left {
                left: 25% !important;
            }

            .follow-form-right {
                left: 75% !important;
            }

            /* Hand position optimization for mobile */
            #leftHand {
                left: -120px;
            }

            #rightHand {
                right: -120px;
            }

            /* Reduce panda size on mobile */
            .panda {
                transform: scale(0.8) !important;
            }
        }
    </style>
</head>

<body>
    <!-- Simple Loading Screen -->
    <div id="loading-screen">
        <div class="spinner-container">
            <div class="loading-spinner"></div>
            <div class="loading-panda">
                <div class="loading-panda-ear left"></div>
                <div class="loading-panda-ear right"></div>
                <div class="loading-panda-face">
                    <div class="loading-panda-eye left"></div>
                    <div class="loading-panda-eye right"></div>
                    <div class="loading-panda-nose"></div>
                </div>
            </div>
        </div>
        <div class="loading-text">Memuat<span class="loading-dots"></span></div>
    </div>

    <div id="container" class="container">
        <!-- FORM SECTION -->
        <div class="row">
            <!-- FORGOT PASSWORD -->
            <div class="col align-items-center flex-col forgot-password">
                <div class="form-wrapper align-items-center">
                    <div class="form forgot-password">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" name="email" placeholder="Email untuk reset password" required>
                            </div>
                            <button type="submit" class="btn-animated">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                Submit
                            </button>
                        </form>
                        <p>
                            <span>
                                Ingat password Anda?
                            </span>
                            <b onclick="toggle()" class="pointer">
                                Login
                            </b>
                        </p>
                    </div>
                </div>
            </div>
            <!-- END FORGOT PASSWORD -->

            <!-- SIGN IN (LOGIN) -->
            <div class="col align-items-center flex-col sign-in">
                <div class="form-wrapper align-items-center">
                    <div class="form sign-in" id="login-form">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="input-group">
                                <i class='bx bx-mail-send'></i>
                                <input type="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="input-group">
                                <i class='bx bxs-lock-alt'></i>
                                <input id="password" type="password" name="password" placeholder="Password" required>
                            </div>
                            <button type="submit" class="btn-animated">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                Log in
                            </button>
                        </form>
                        <p>
                            <b onclick="toggle()" class="pointer">
                                Lupa Password?
                            </b>
                        </p>
                        <p>
                            <span>
                                Tidak ingin login?
                            </span>
                            <b onclick="window.location.href='/';" class="pointer">
                                Kembali ke halaman utama
                            </b>
                        </p>
                    </div>
                </div>
            </div>
            <!-- END SIGN IN -->
            <!-- PANDA ANIMATION -->
            <div class="panda-container">
                <div class="panda">
                    <div class="ear"></div>
                    <div class="face">
                        <div class="eye-shade"></div>
                        <div class="eye-white">
                            <div class="eye-ball"></div>
                        </div>
                        <div class="eye-shade rgt"></div>
                        <div class="eye-white rgt">
                            <div class="eye-ball"></div>
                        </div>
                        <div class="nose"></div>
                        <div class="mouth"></div>
                    </div>
                    <div class="body"></div>
                    <div class="foot">
                        <div class="finger"></div>
                    </div>
                    <div class="foot rgt">
                        <div class="finger"></div>
                    </div>
                    <div class="hand" id="leftHand"></div>
                    <div class="hand rgt" id="rightHand"></div>
                </div>
            </div>
        </div>
        <!-- END FORM SECTION -->

        <!-- CONTENT SECTION -->
        <div class="row content-row">
            <!-- SIGN IN CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-in">
                    <h2>
                        Selamat Datang
                    </h2>
                </div>
                <div class="img sign-in">
                </div>
            </div>
            <!-- END SIGN IN CONTENT -->

            <!-- FORGOT PASSWORD CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="img forgot-password">
                </div>
                <div class="text forgot-password">
                    <h2>
                        Reset Password
                    </h2>
                </div>
            </div>
            <!-- END FORGOT PASSWORD CONTENT -->
        </div>
        <!-- END CONTENT SECTION -->
    </div>

    <!-- SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Sweet Alert -->
    <script>
        // Loading screen handler
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading screen after content is fully loaded
            setTimeout(function() {
                const loadingScreen = document.getElementById('loading-screen');
                if (loadingScreen) {
                    loadingScreen.style.opacity = '0';
                    setTimeout(function() {
                        loadingScreen.style.visibility = 'hidden';
                    }, 500);
                }
            }, 800); // Show loading for a minimum of 800ms for better UX
        });

        // Failsafe to ensure loading screen is removed even if page load is slow
        setTimeout(function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen && loadingScreen.style.visibility !== 'hidden') {
                loadingScreen.style.opacity = '0';
                setTimeout(function() {
                    loadingScreen.style.visibility = 'hidden';
                }, 500);
            }
        }, 3000);

        // Menggunakan JavaScript untuk memeriksa nilai session yang sudah diteruskan dari PHP
        const successMessage = "{{ session('success') }}";

        // Jika ada pesan sukses, tampilkan SweetAlert
        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: successMessage,
                timer: 8000,
                timerProgressBar: true
            });
        }

        // Sweet Alert error email dan password
        const errorMessage = "{{ session('error') }}";

        // Jika ada pesan error, tampilkan SweetAlert
        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: errorMessage,
                timer: 8000,
                timerProgressBar: true
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            // Penempatan awal panda - pastikan posisinya benar
            setTimeout(() => {
                container.classList.add('sign-in');
                $('.panda-container').addClass('follow-form-right');
                $('.panda-container').removeClass('follow-form-left');

                // Pastikan form login terlihat dan form lupa password tersembunyi saat awal
                $('.col.sign-in .form').css('display', 'block');
                $('.col.forgot-password .form').css('display', 'none');

                // Posisikan panda untuk memegang form - coba beberapa kali untuk memastikan bekerja dengan baik
                positionPandaHands();

                // Set posisi awal tangan - memegang form di bawah (posisi default)
                holdForm();

                // Pastikan form tidak menutupi mata pada awalnya
                $('.form').removeClass('password-focus');

                // Pastikan form berada di posisi yang tepat
                setTimeout(function() {
                    positionPandaHands();

                    // Animasikan kemunculan form dengan halus
                    $('.form.sign-in').css({
                        'transition': 'all 0.6s ease-in-out',
                        'transform': 'scale(1)'
                    });

                    // Pastikan tangan berada di posisi bawah
                    holdForm();
                }, 300);

                setTimeout(function() {
                    positionPandaHands();
                    holdForm();
                }, 800);
            }, 200); // Efek ketika field password mendapat fokus - panda menutupi matanya
            $('#password').focusin(function() {
                $('.form.sign-in').addClass('up');

                // Gerakkan tangan untuk mengangkat formulir menutupi mata panda
                coverPandaEyes();

                // Pastikan panda tetap di posisi normal
                $('.panda-container').css({
                    'transform': 'translate(-50%, -50%)',
                    'transition': 'all 0.4s ease-in-out'
                });
            });

            // Efek ketika field password kehilangan fokus - tangan kembali memegang form
            $('#password').focusout(function() {
                $('.form.sign-in').removeClass('up');

                // Kembalikan tangan ke posisi normal dan turunkan formulir kembali
                holdForm();

                // Panda tetap di posisi normal
                $('.panda-container').css({
                    'transform': 'translate(-50%, -50%)',
                    'transition': 'all 0.4s ease-in-out'
                });
            }); // Efek ketika pointer mouse masuk ke kolom password - panda siap menutupi mata
            $('#password').mouseenter(function() {
                // Jika belum dalam fokus, persiapkan tangan untuk mengangkat form
                if (!$(this).is(':focus')) {
                    // Gerakkan tangan panda untuk persiapan menutupi mata
                    $('#leftHand').css({
                        'transform': 'rotate(80deg) translateX(20px) translateY(0px)',
                        'transition': 'all 0.3s ease-in-out'
                    });
                    $('#rightHand').css({
                        'transform': 'rotate(-80deg) translateX(-20px) translateY(0px)',
                        'transition': 'all 0.3s ease-in-out'
                    });

                    // Tidak lagi mengubah posisi form saat hover
                    // Form berubah hanya saat fokus, tidak saat hover
                } else {
                    // Jika sudah fokus, terapkan efek penuh
                    coverPandaEyes();
                }

                // Panda tetap di posisi normal
                $('.panda-container').css({
                    'transform': 'translate(-50%, -50%)',
                    'transition': 'all 0.3s ease-in-out'
                });
            });

            // Efek ketika pointer mouse meninggalkan kolom password - tangan kembali normal
            $('#password').mouseleave(function() {
                // Jika password tidak sedang dalam fokus, kembalikan tangan ke posisi memegang form
                if (!$(this).is(':focus')) {
                    holdForm();

                    // Panda tetap di posisi normal
                    $('.panda-container').css({
                        'transform': 'translate(-50%, -50%)',
                        'transition': 'all 0.3s ease-in-out'
                    });
                }
            });

            // Function untuk menutupi mata panda ketika password field diklik
            // Tangan panda mengangkat form ke atas
            function coverPandaEyes() {
                // Gerakkan tangan panda ke atas untuk mengangkat formulir
                $('#leftHand').css({
                    'transform': 'rotate(120deg) translateX(40px) translateY(-90px)',
                    'transition': 'all 0.4s ease-in-out'
                });
                $('#rightHand').css({
                    'transform': 'rotate(-120deg) translateX(-40px) translateY(-90px)',
                    'transition': 'all 0.4s ease-in-out'
                });

                // Formulir diangkat ke atas untuk menutupi mata panda
                let activeForm;
                if (container.classList.contains('sign-in')) {
                    activeForm = $('.col.sign-in .form');
                    // Pastikan form aktif terlihat dan yang lain tersembunyi
                    $('.col.forgot-password .form').css('display', 'none');
                } else {
                    activeForm = $('.col.forgot-password .form');
                    // Pastikan form aktif terlihat dan yang lain tersembunyi
                    $('.col.sign-in .form').css('display', 'none');
                }

                // Display form before applying animation
                activeForm.css('display', 'block');

                // Apply animation with a slight delay to ensure display is set first
                setTimeout(function() {
                    activeForm.addClass('password-focus');
                }, 10);
            }

            // Function untuk memegang form (posisi default ketika tidak di password field)
            // Tangan panda menurunkan form ke badan
            function holdForm() {
                // Posisikan tangan untuk memegang form di bawah
                $('#leftHand').css({
                    'transform': 'rotate(-30deg) translateX(-5px) translateY(60px)',
                    'transition': 'all 0.4s ease-in-out'
                });
                $('#rightHand').css({
                    'transform': 'rotate(30deg) translateX(5px) translateY(60px)',
                    'transition': 'all 0.4s ease-in-out'
                });

                // Turunkan formulir kembali ke posisi normal
                $('.form').removeClass('password-focus');
            }

            // Alias untuk kompatibilitas dengan kode lama
            function restoreHandPosition() {
                holdForm();
            }

            // Efek ketika pointer mouse masuk ke kolom email - pastikan tangan memegang form
            $('input[type="email"]').mouseenter(function() {
                // Jika password tidak sedang dalam fokus, kembalikan tangan ke posisi memegang form
                if (!$('#password').is(':focus')) {
                    holdForm();
                }
            });

            // Efek ketika field email mendapat fokus - tangan memegang form
            $('input[type="email"]').focusin(function() {
                holdForm();
            });

            // Mengatur ulang posisi panda saat ukuran layar berubah
            $(window).resize(function() {
                // Reposisi panda berdasarkan status form saat ini
                if (container.classList.contains('sign-in')) {
                    $('.panda-container').removeClass('follow-form-left');
                    $('.panda-container').addClass('follow-form-right');
                } else {
                    $('.panda-container').removeClass('follow-form-right');
                    $('.panda-container').addClass('follow-form-left');
                }

                // Hitung ulang posisi tangan panda agar sesuai dengan form
                positionPandaHands();
            });

            // Gerakan mata panda mengikuti pergerakan mouse
            $(document).on("mousemove", function(event) {
                var dw = $(document).width() / 15;
                var dh = $(document).height() / 15;
                var x = event.pageX / dw;
                var y = event.pageY / dh;
                $('.eye-ball').css({
                    width: x,
                    height: y
                });
            });

            // Posisikan panda saat semua aset telah dimuat
            $(window).on('load', function() {
                positionPandaHands();
                setTimeout(positionPandaHands, 200);
            });
        });

        // Fungsi toggle antara login dan lupa password
        let container = document.getElementById('container');

        function toggle() {
            // Tambahkan animasi halus pada panda selama transisi
            $('.panda-container').css('transition', 'all 1s ease-in-out');

            // Toggle kelas terlebih dahulu
            container.classList.toggle('sign-in');
            container.classList.toggle('forgot-password');

            // Kemudian update posisi panda setelah toggle
            if (container.classList.contains('sign-in')) {
                // Berpindah ke sign-in (login) - panda di kanan
                $('.panda-container').removeClass('follow-form-left');
                $('.panda-container').addClass('follow-form-right');

                // Pastikan form yang tepat terlihat
                $('.col.sign-in .form').css('display', 'block');
                $('.col.forgot-password .form').css('display', 'none');
            } else {
                // Berpindah ke forgot-password (lupa password) - panda di kiri
                $('.panda-container').removeClass('follow-form-right');
                $('.panda-container').addClass('follow-form-left');

                // Pastikan form yang tepat terlihat
                $('.col.sign-in .form').css('display', 'none');
                $('.col.forgot-password .form').css('display', 'block');
            }

            // Reset status fokus saat beralih form
            $('.form.sign-in').removeClass('up');
            $('.form').removeClass('password-focus');

            // Reset posisi tangan ke posisi bawah (memegang form)
            holdForm();

            // Reposisi panda setelah beberapa penundaan untuk membiarkan transisi form selesai
            setTimeout(function() {
                positionPandaHands();
                holdForm(); // Pastikan tangan memegang form setelah toggle
            }, 100);
            setTimeout(function() {
                positionPandaHands();
                holdForm(); // Pastikan tangan memegang form setelah toggle
            }, 500);
            setTimeout(function() {
                positionPandaHands();
                holdForm(); // Pastikan tangan memegang form setelah toggle
            }, 1000);
        } // Fungsi untuk memposisikan panda dan form dengan benar
        function positionPandaHands() {
            // Hitung posisi form - khususnya target elemen form
            let formElement;

            if (container.classList.contains('sign-in')) {
                // Untuk form login (sisi kanan)
                formElement = $('.col.sign-in .form-wrapper .form');

                // Pastikan form yang tepat terlihat
                $('.col.sign-in .form').css('display', 'block');
                $('.col.forgot-password .form').css('display', 'none');
            } else {
                // Untuk form lupa password (sisi kiri)
                formElement = $('.col.forgot-password .form-wrapper .form');

                // Pastikan form yang tepat terlihat
                $('.col.sign-in .form').css('display', 'none');
                $('.col.forgot-password .form').css('display', 'block');
            }

            if (formElement.length) {
                // Cek apakah tampilan mobile atau desktop
                if ($(window).width() <= 425) {
                    // Mobile view - pastikan panda berada di posisi yang tepat di atas form
                    $('.panda-container').css({
                        'top': '80px',
                        'transform': 'translate(-50%, 0)'
                    });

                    // Sesuaikan margin form pada mobile - buat lebih besar agar form berada di badan panda
                    formElement.css({
                        'margin-top': '200px',
                        'transition': 'all 0.4s ease-in-out'
                    });
                } else {
                    // Desktop view - panda di tengah layar, form di bawah mata panda
                    $('.panda-container').css({
                        'top': '30%',
                        'transform': 'translate(-50%, -50%)'
                    });

                    // Sesuaikan margin-top dari form untuk berada di badan panda
                    formElement.css({
                        'margin-top': '180px',
                        'transition': 'all 0.4s ease-in-out'
                    });
                }

                // Tampilkan form aktif dengan scale yang benar
                formElement.css('transform', 'scale(1)');

                // Handle password field focus
                if ($('#password').is(':focus')) {
                    // Delay sedikit untuk pastikan form sudah visible sebelum diangkat
                    setTimeout(function() {
                        coverPandaEyes();
                    }, 10);
                } else {
                    // Pastikan tangan di posisi default (mengarah ke bawah)
                    holdForm();
                }
            }
        }
    </script>
</body>

</html>
