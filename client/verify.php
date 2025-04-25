<?php 
include('commonfiles.php');
?>

  <div class="container">
    <?php
    session_start();
    if (isset($_SESSION['message'])) {
      echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['message']) . '</div>';
      unset($_SESSION['message']);
    }
    ?>
    <h1 class="heading">OTP Verification</h1>
    <form method="POST" action="../server/requests.php">
      
      <div class="row">
        <div class="col-6 offset-sm-3 margin-bottom-15">
          <label for="otp" class="form-label">Enter OTP <sup>*</sup></label>
          <input type="number" name="otp" class="form-control" id="otp-id" placeholder="Enter OTP" maxlength="6" 
          inputmode="numeric" required>
        </div>
      </div>
      
      <div class="row">
        <div class="col-2 offset-sm-5">
          <button type="submit" name="otp-ver" class="btn btn-primary">Submit</button>
        </div>
      </div>

      
      <style>
        .custom-alert {
          background-color: #28a745;
          color: white;
          text-align: center;
          font-weight: bold;
          border-radius: 10px;
          padding: 10px;
          margin-bottom: 20px;
        }

        .heading {
          text-align: center;
        }
        .margin-bottom-15 {
          margin-bottom: 15px;
        }
        
        .container{
          background: black;
          /* background: url('./public/bg_image.jpeg') no-repeat center center fixed; */
          color: white;
          margin-top: 100px;
          height: 250px;
        }

        .row{
          margin-bottom: 15px;
          
        }
        
        .btn {
          --border-color: linear-gradient(-45deg, #ffae00, #7e03aa, #00fffb);
          --border-width: 0.125em;
          --curve-size: 0.5em;
          --blur: 30px;
          --bg: #080312;
          --color: #afffff;
          color: var(--color);
          cursor: pointer;
          position: relative;
          isolation: isolate;
          display: inline-grid;
          place-content: center;
          padding: 0.5em 1.5em;
          font-size: 17px;
          border: 0;
          text-transform: uppercase;
          box-shadow: 10px 10px 20px rgba(0, 0, 0, 0.6);
          clip-path: polygon(
            0% var(--curve-size),
            var(--curve-size) 0,
            100% 0,
            100% calc(100% - var(--curve-size)),
            calc(100% - var(--curve-size)) 100%,
            0 100%
            );
            transition: color 250ms;
          }
          
          
          .btn::after,
          .btn::before {
            content: "";
            position: absolute;
            inset: 0;
        }

        .btn::before {
          background: var(--border-color);
          background-size: 300% 300%;
          animation: move-bg7234 5s ease infinite;
          z-index: -2;
        }

        @keyframes move-bg7234 {
          0% {
            background-position: 31% 0%;
          }
          
          50% {
            background-position: 70% 100%;
          }
          
          100% {
            background-position: 31% 0%;
          }
        }


        .btn::after {
          background: var(--bg);
          z-index: -1;
          clip-path: polygon(
            /* Top-left */ var(--border-width)
            calc(var(--curve-size) + var(--border-width) * 0.5),
            calc(var(--curve-size) + var(--border-width) * 0.5) var(--border-width),
            /* top-right */ calc(100% - var(--border-width)) var(--border-width),
            calc(100% - var(--border-width))
            calc(100% - calc(var(--curve-size) + var(--border-width) * 0.5)),
            /* bottom-right 1 */
            calc(100% - calc(var(--curve-size) + var(--border-width) * 0.5))
            calc(100% - var(--border-width)),
            /* bottom-right 2 */ var(--border-width) calc(100% - var(--border-width))
          );
          transition: clip-path 500ms;
        }

        .btn:where(:hover, :focus)::after {
          clip-path: polygon(
            /* Top-left */ calc(100% - var(--border-width))
            calc(100% - calc(var(--curve-size) + var(--border-width) * 0.5)),
            calc(100% - var(--border-width)) var(--border-width),
            /* top-right */ calc(100% - var(--border-width)) var(--border-width),
            calc(100% - var(--border-width))
            calc(100% - calc(var(--curve-size) + var(--border-width) * 0.5)),
            /* bottom-right 1 */
            calc(100% - calc(var(--curve-size) + var(--border-width) * 0.5))
            calc(100% - var(--border-width)),
            /* bottom-right 2 */
            calc(100% - calc(var(--curve-size) + var(--border-width) * 0.5))
            calc(100% - var(--border-width))
            );
          transition: 200ms;
        }
        
        .btn:where(:hover, :focus) {
          color: #fff;
        }


        /* input field */

        .form-control {
          --border-radius: 15px;
          --border-width: 4px;
          
          position: relative;
          padding: 1em 2em;
          border: none;
          background-color: #cccccc;
          font-family: "Roboto", Arial, "Segoe UI", sans-serif;
          font-size: 18px;
          font-weight: 500;
          color: #fff;
          border-radius: var(--border-radius);
          outline: none;
          z-index: 0;
          
          transition: box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .form-control:focus {
          box-shadow: 0 0 20px #ff3700, 0 0 25px #ff601b;
          background-color: #2a2a2a;
          color: white;
        }

        .form-control::before {
          content: "";
          position: absolute;
          z-index: -1;
          top: calc(-1 * var(--border-width));
          left: calc(-1 * var(--border-width));
          width: calc(100% + calc(var(--border-width) * 2));
          height: calc(100% + calc(var(--border-width) * 2));
          background: conic-gradient(
            #488cfb,
            #29dbbc,
            #ddf505,
            #ff9f0e,
            #e440bb,
            #655adc,
            #488cfb
          );
          border-radius: calc(var(--border-radius) + var(--border-width));
          animation: rotate-hue 3s linear infinite;
          filter: hue-rotate(0deg);
          transition: filter 0.3s ease-in-out;
        }

        .form-control:hover::before {
          animation-play-state: running;
        }
        
        @keyframes rotate-hue {
          to {
            filter: hue-rotate(360deg);
          }
        }
      </style>
      <script>
        setTimeout(() => {
          const alert = document.querySelector('.alert-success');
          if (alert) alert.style.display = 'none';
        }, 3000);
      </script>
    </form>
  </div>