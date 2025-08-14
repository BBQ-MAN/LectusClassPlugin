<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e("수료증", "lectus-class-system"); ?></title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        body {
            font-family: "Noto Sans KR", sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .certificate {
            width: 1024px;
            background: white;
            padding: 60px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            border: 10px solid #gold;
        }
        .certificate::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            width: 120px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 48px;
            color: #333;
            margin: 0;
            font-weight: 300;
            letter-spacing: 5px;
        }
        .content {
            text-align: center;
            margin: 40px 0;
        }
        .recipient {
            font-size: 36px;
            color: #667eea;
            margin: 20px 0;
            font-weight: bold;
        }
        .course-title {
            font-size: 28px;
            color: #555;
            margin: 20px 0;
        }
        .description {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            margin: 30px auto;
            max-width: 600px;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid #eee;
        }
        .signature {
            text-align: center;
            flex: 1;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #333;
            margin: 0 auto 10px;
            height: 40px;
        }
        .signature-name {
            font-size: 14px;
            color: #666;
        }
        .certificate-info {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }
        .certificate-number {
            font-family: monospace;
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        .issued-date {
            font-size: 16px;
            color: #666;
            margin-top: 20px;
        }
        @media print {
            body {
                background: white;
            }
            .certificate {
                box-shadow: none;
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <h1><?php _e("수료증", "lectus-class-system"); ?></h1>
        </div>
        
        <div class="content">
            <p class="description"><?php _e("이 수료증은 아래 명시된 과정을 성공적으로 완료하였음을 증명합니다", "lectus-class-system"); ?></p>
            
            <div class="recipient"><?php echo esc_html($user->display_name); ?></div>
            
            <p class="description"><?php _e("님께서", "lectus-class-system"); ?></p>
            
            <div class="course-title"><?php echo esc_html($course->post_title); ?></div>
            
            <p class="description"><?php _e("과정을 성공적으로 수료하였음을 증명합니다", "lectus-class-system"); ?></p>
            
            <div class="issued-date">
                <?php echo date_i18n("Y년 n월 j일", strtotime($certificate->issued_at)); ?>
            </div>
        </div>
        
        <div class="footer">
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-name"><?php echo get_bloginfo("name"); ?></div>
            </div>
        </div>
        
        <div class="certificate-info">
            <div class="certificate-number">
                <?php _e("수료증 번호:", "lectus-class-system"); ?> <?php echo esc_html($certificate->certificate_number); ?>
            </div>
            <div>
                <?php _e("발급일:", "lectus-class-system"); ?> <?php echo date_i18n(get_option("date_format"), strtotime($certificate->issued_at)); ?>
            </div>
        </div>
    </div>
    
    <script>
    window.onload = function() {
        if (window.location.search.includes("download=pdf")) {
            window.print();
        }
    }
    </script>
</body>
</html>