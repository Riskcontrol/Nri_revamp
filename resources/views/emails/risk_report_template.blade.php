<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        /* Base Resets */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }

        /* Container styling */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f6;
            padding-bottom: 40px;
        }

        .main-content {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }

        /* Header / Logo Area */
        .header {
            background-color: #0f172a;
            /* Dark Navy Background */
            padding: 30px 20px;
            text-align: center;
        }

        .header img {
            max-height: 60px;
            /* Adjust based on your logo aspect ratio */
            width: auto;
        }

        /* Content Body */
        .content-body {
            padding: 40px 30px;
            color: #334155;
            line-height: 1.6;
            font-size: 16px;
        }

        h2 {
            color: #0f172a;
            margin-top: 0;
            font-size: 20px;
        }

        /* Data Summary Box */
        .summary-box {
            background-color: #f1f5f9;
            border-left: 4px solid #10b981;
            /* Emerald Green Accent */
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .summary-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary-box li {
            margin-bottom: 5px;
            color: #475569;
        }

        .summary-box li:last-child {
            margin-bottom: 0;
        }

        /* Links */
        a {
            color: #10b981;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding-top: 30px;
            padding-bottom: 30px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="main-content">

            {{-- Header with Logo --}}
            <div class="header">
                {{-- REPLACE THE SRC BELOW WITH YOUR ACTUAL LOGO URL (e.g. https://yourwebsite.com/images/logo.png) --}}
                {{-- If you don't have a live URL yet, you can use the cid attachment method, but a public URL is easier --}}
                <img src="https://via.placeholder.com/200x60/0f172a/ffffff?text=NRI+PROJECT" alt="NRI Project Logo">
            </div>

            <div class="content-body">
                <h2>Your Risk Assessment Report</h2>

                <p>Hello,</p>

                <p>Thank you for using the <strong>NRI Risk Tool</strong>. Attached to this email is the detailed risk
                    profile you requested.</p>

                <div class="summary-box">
                    <ul>
                        <li><strong>LGA:</strong> {{ $lga }}</li>
                        <li><strong>Year:</strong> {{ $year }}</li>
                    </ul>
                </div>

                <p>The report contains critical insights regarding risk distribution, casualty data, and neighbourhood
                    hotspots.</p>

                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">

                <p style="font-size: 15px;">
                    If you have any further questions or need more detailed analysis, please visit
                    <a href="https://nigeriariskindex.com" target="_blank">nigeriariskindex.com</a>
                    or send a message to
                    <a href="mailto:info@riskcontrolnigeria.com">info@riskcontrolnigeria.com</a>.
                </p>

                <p style="margin-top: 30px;">
                    Best Regards,<br>
                    <strong>NRI Team</strong>
                </p>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Risk Control Nigeria. All rights reserved.
        </div>
    </div>
</body>

</html>
