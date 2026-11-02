<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store Website</title>
    
    <link rel="stylesheet" href="../Bookpalace/style.css?v=<?php echo rawurlencode((string) filemtime(dirname(__DIR__) . '/Bookpalace/style.css')); ?>">
    <style>
        .stand .stand-img{
          height:auto;
          max-height: 200px;
          width: 100%;
          transition:1s;
        }

        .stand .stand-img:hover {
          transform:scale(1.2);
        }

        .best-seller .best-seller-details img{
          transition:1s;
        }

        .best-seller .best-seller-details img:hover{
          transform:scale(1.2);
        }
    </style>
    <link rel="stylesheet" href="../assets/css/page-transitions.css?v=20261101-smooth2">
    <script defer src="../assets/js/page-transitions.js?v=20261101-smooth2"></script>
</head>
<body>  
    <?php include 'userheader.php'?> 
    <section class="home-hero">
        <div class="main home-hero__layout">
            <div class="main_tag home-hero__copy">
                <h1>WELCOME TO<br><span><i>BOOK PALACE</i></span></h1>
                <p>
                    <i>Books are the constant companions of those who find solace 
                        in the stories
                        <br>
                        A book a day keeps the reality away!
                    </i>
                </p>
            </div>

            <div class="main_img home-hero__media">
                <img class="home-hero__image" src="../Photos/table 2.png" alt="Books arranged on a reading table" decoding="async" fetchpriority="high">
            </div>
        </div>
    </section>
    <br><br><br>

    <h1 style="font-weight: bolder; text-align: center;">စာအုပ်အမျိုးအစားများ (Book Categories)</h1>
    <div class="category-showcase">
    <div class="stand">
        <img class="stand-img" src="../Photos/stand 1.png" alt="" loading="lazy" decoding="async" />
        <a>ရသ & သုတ</a>
    </div>
    <div class="stand">
        <img class="stand-img" src="../Photos/stand 2.png" alt="" loading="lazy" decoding="async" />
        <a>နိုင်ငံရေး & သမိုင်း</a>
    </div>
    <div class="stand">
        <img class="stand-img" src="../Photos/stand3.png" alt="" loading="lazy" decoding="async" />
        <a>ကလေးစာပေ</a>
    </div>
    <div class="stand">
        <img class="stand-img" src="../Photos/stand 4.png" alt="" loading="lazy" decoding="async" />
        <a>စာနယ်ဇင်း & မဂ္ဂဇင်း</a>
    </div>
    <div class="stand">
        <img class="stand-img" src="../Photos/stand 5.png" alt="" loading="lazy" decoding="async" />
        <a>ကျန်းမာရေး</a>
    </div>
    <div class="stand">
        <img class="stand-img" src="../Photos/stand 6.png" alt="" loading="lazy" decoding="async" />
        <a>LGBT, Romance စာအုပ်များ</a>
    </div>
    </div>
    <br><br><br>

    <h1 style="text-align: center;">အရောင်းရဆုံး စာအုပ်များ (Best Seller)</h1>
    <div class="book-showcase">
    <div class="best-seller">
        <div class="best-seller-details">
            <a href="userindex.php"><img src="../Photos/Lgbt 8.jpg" alt="" width="300" height="250" loading="lazy" decoding="async"></a>
        </div>
    </div>

    <div class="best-seller">
        <div class="best-seller-details">
            <a href="userindex.php"><img src="../Photos/health 10.png" alt="" width="300" height="250" loading="lazy" decoding="async"></a>
        </div>
    </div>

    <div class="best-seller">
        <div class="best-seller-details">
            <img src="../Photos/national 1.png" alt="" width="300" height="250" loading="lazy" decoding="async">
        </div>
    </div>

    <div class="best-seller">
        <div class="best-seller-details">
            <img src="../Photos/yatha 5.png" alt="" width="300" height="250" loading="lazy" decoding="async">
        </div>
    </div>
    </div>
    <br>
    <button class="btn-book"><a href="bestseller.php">ပိုမိုရှာရန်</a></button>
    <br><br>

    <h1 style="text-align: center;">အသစ်ထွက် စာအုပ်များ (New Releases)</h1>

    <div class="book-showcase">
    <div class="best-seller">
        <div class="best-seller-details">
            <img src="../Photos/national 10.png" alt="" width="300" height="250" loading="lazy" decoding="async">
        </div>
    </div>

    <div class="best-seller">
        <div class="best-seller-details">
            <a href="userindex.php"><img src="../Photos/yatha 18.png" alt="" width="300" height="250" loading="lazy" decoding="async"></a>
        </div>
    </div>

    <div class="best-seller">
        <div class="best-seller-details">
            <a href="userindex.php"><img src="../Photos/children 11.png" alt="" width="300" height="250" loading="lazy" decoding="async"></a>
        </div>
    </div>

    <div class="best-seller">
        <div class="best-seller-details">
            <img src="../Photos/yatha10.png" alt="" width="300" height="250" loading="lazy" decoding="async">
        </div>
    </div>
    </div>

    <br>
    <button class="btn-book"><a href="newrelease.php">ပိုမိုရှာရန်</a></button>
    <br><br><br>

    <h2 style="text-align: center;">ဝန်ဆောင်မှုများ (Services)</h2><br>
    <div class="wh-card">
        <div class="card">
            <img src="../Photos/Delivery.png" alt="" width="350" height="200" loading="lazy" decoding="async">
            <div class="card__content">
                <p class="card__title">ပို့ဆောင်မှုကြာမြင့်ချိန် (Delivery Time)</p>
                <p class="card__description">အမှာအတည်ပြုပြီးချိန်မှ ၂ နာရီအတွင်း ထုပ်ပိုးပြီးစီးပါသည်။ ရုံးဖွင့်ရက်များတွင် ၂၄ နာရီအတွင်း စတင်ပို့ဆောင်ပေးပါသည်။ ရန်ကုန်မြို့တွင်း ၃ ရက်မှ ၅ ရက်အတွင်း၊ နယ်မြို့များသို့ ၅ ရက်မှ ၇ ရက်အတွင်း၊ ပြည်ပနိုင်ငံများသို့ ၅ ရက်မှ ၁၀ ရက်အတွင်း စာအုပ်များရောက်ရှိနိုင်ပါသည်။</p>
            </div>
        </div>
        <div class="card">
            <img src="../Photos/payment.png" alt="" width="350" height="200" loading="lazy" decoding="async">
            <div class="card__content">
                <p class="card__title">ပို့ဆောင်ခ (Delivery Charges)</p>
                <p class="card__description">ပြည်တွင်းပို့ဆောင်မှုအတွက် ပထမစာအုပ် ၂၀၀၀ ကျပ်နှင့် ထပ်တိုးစာအုပ်တစ်အုပ်လျှင် ၁၀၀၀ ကျပ် ကောက်ခံပါသည်။ လေကြောင်းပို့ဆောင်မှု သို့မဟုတ် နယ်မြို့များသို့ပို့ဆောင်မှုအတွက် ပို့ဆောင်ခ အနည်းငယ်ပိုနိုင်ပါသည်။ ပြည်ပပို့ဆောင်မှုအတွက် နိုင်ငံအကွာအဝေးနှင့် အလေးချိန်ပေါ်မူတည်၍ နှုန်းထားကွာခြားနိုင်ပါသည်။</p>
            </div>
        </div>
        <div class="card">
            <img src="../Photos/gift.png" alt="" width="230" height="200" loading="lazy" decoding="async">
            <div class="card__content">
                <p class="card__title">စာအုပ်ဖုံး၊ စာမှတ်ကတ် အခမဲ့လက်ဆောင် (Gift)</p>
                <p class="card__description">စာအုပ်တစ်အုပ်စီအတွက် စာမှတ်ကတ်နှင့် စာအုပ်ဖုံးလေးများကို လက်ဆောင်အဖြစ် အခမဲ့ပေးပါသည်။ ဖတ်လက်စနေရာကို မှတ်သားရန်လည်းကောင်း၊ စာအုပ်ကို သန့်ရှင်းစွာ ထိန်းသိမ်းရန်လည်းကောင်း အထောက်အကူဖြစ်စေပါသည်။</p>
            </div>
        </div>
    </div>

    <br><br>
    <?php include 'userfooter.php' ?>
</body>
</html>
