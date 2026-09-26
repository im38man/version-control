const allVideos = [
    { 
        title: "高嶺のなでしこ「〜CROWNED IN BLOOM〜」ワンマンライブ (2026-09-13)", 
        category: "special", 
        date: "2026.09.13", 
        views: 16341, 
        duration: "01:15:58", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/2oiUJVIaffA.jpg" 
    },
    { 
        title: "【MV】僕らの青／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2026.08.24", 
        views: 76942, 
        duration: "03:09", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/dtBq4Yo57rk.jpg" 
    },
    { 
        title: "【LIVE】病名恋ワズライ／高嶺のなでしこ Live Tour - Bouquet of 9 Flowers - FINAL", 
        category: "special", 
        date: "2026.08.23", 
        views: 22347 , 
        duration: "04:53", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/q2dqKruOPsw.jpg" 
    },
    { 
        title: "【Dance Performance Video】ハートブーケ／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2026.06.07", 
        views: 181000, 
        duration: "04:16", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/Vlpqp4QA61Y.jpg" 
    },
    { 
        title: "【MV】生きてりゃいい／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2026.05.13", 
        views: 325000, 
        duration: "03:34", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/957yJCyB-tk.jpg" 
    },
    { 
        title: "【Dance Performance Video】私は、わたしの事が好き。／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2026.04.14", 
        views: 353000, 
        duration: "04:10", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/IoCC9aOnX-E.jpg" 
    },
    { 
        title: "【Dance Performance Video】世界は恋に落ちている／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2026.03.15", 
        views: 339020, 
        duration: "05:12", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/RayWMQMSkeI.jpg" 
    },
    { 
        title: "【LIVE】推しの魔法／高嶺のなでしこ 3rd ANNIVERSARY CONCERT「A Wonderful Encounter」", 
        category: "special", 
        date: "2026.02.04", 
        views: 85724 , 
        duration: "06:05", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/Cbv2fbWhMGg.jpg" 
    },
    { 
        title: "【LIVE】可愛くてごめん／高嶺のなでしこ 3rd ANNIVERSARY CONCERT「A Wonderful Encounter」", 
        category: "special", 
        date: "2026.01.27", 
        views: 454843 , 
        duration: "03:40", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/VI1n97ZC3mw.jpg" 
    },
    { 
        title: "【LIVE】革命の女王 ~ 決戦スピリット／高嶺のなでしこ 3rd ANNIVERSARY CONCERT「A Wonderful Encounter」", 
        category: "special", 
        date: "2026.01.17", 
        views: 188998 , 
        duration: "08:36", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/__rgL3hLhkI.jpg" 
    },
    { 
        title: "【LIVE】初恋のこたえ。／高嶺のなでしこ 3rd ANNIVERSARY CONCERT「A Wonderful Encounter」", 
        category: "special", 
        date: "2026.01.08", 
        views: 149148  , 
        duration: "03:55", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/gXPTUUR0dDc.jpg" 
    },
    { 
        title: "【LIVE】花は誓いを忘れない／高嶺のなでしこ 3rd ANNIVERSARY CONCERT「A Wonderful Encounter」", 
        category: "special", 
        date: "2025.12.25", 
        views: 105625  , 
        duration: "03:34", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/cg5bcITxD20.jpg" 
    },
    { 
        title: "【MV】花は誓いを忘れない／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.12.05", 
        views: 444886  , 
        duration: "04:07", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/f1ss7kWQnY8.jpg" 
    },
    { 
        title: "【Dance Performance Video】病名恋ワズライ／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.11.20", 
        views: 636906  , 
        duration: "04:17", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/rYlY5-Wv_xA.jpg" 
    },
    { 
        title: "高嶺のなでしこ 3rd ANNIVERSARY CONCERT 「A Wonderful Encounter」2025-09-07", 
        category: "special", 
        date: "2025.09.07", 
        views: 38238  , 
        duration: "02:23:18", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/qukuSmPyesU.jpg" 
    },
    { 
        title: "【LIVE】アイドル衣装／『高嶺のなでしこ 東名阪ツアー 2025 - Spring Ride -』", 
        category: "special", 
        date: "2025.09.03", 
        views: 57711  , 
        duration: "04:03", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/srRzKsh_lMY.jpg" 
    },
    { 
        title: "【MV】この世界は噓でできている／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.08.20", 
        views: 452532   , 
        duration: "04:57", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/pfrBPp_btAQ.jpg" 
    },
    { 
        title: "高嶺のなでしこ「3rdファンミーティング〜私たちの宣言式〜 」1部", 
        category: "special", 
        date: "2025.08.07", 
        views: 1234567, 
        duration: "128:36", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/mSjZM5ob_jg.jpg" 
    },
    { 
        title: "【LIVE】可愛くてごめん／『高嶺のなでしこ ワンマンライブ 2025 〜Cute for life〜』supported by KOJI", 
        category: "special", 
        date: "2025.07.12", 
        views: 3731788, 
        duration: "03:40", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/_ICAYT8antM.jpg" 
    },
    { 
        title: "【MV】ライフクエスト／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.07.02", 
        views: 557519, 
        duration: "03:19", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/5ezn2DJbK7s.jpg" 
    },
    { 
        title: "【LIVE】美しく生きろ／『高嶺のなでしこ ワンマンライブ 2025 〜Cute for life〜』supported by KOJI", 
        category: "special", 
        date: "2025.06.28", 
        views: 433048, 
        duration: "04:19", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/2n04nAnKI3Y.jpg" 
    },
    { 
        title: "【MV】初恋のこたえ。／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.06.18", 
        views: 1324011, 
        duration: "03:59", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/MdzIkBvRMC0.jpg" 
    },
    { 
        title: "【LIVE】初恋のひと。／『高嶺のなでしこ ワンマンライブ 2025 〜Cute for life〜』supported by KOJI", 
        category: "special", 
        date: "2025.06.14", 
        views: 742810 , 
        duration: "03:37", 
        tag: "LIVE",
        thumb: "高嶺のなでしこ/img/o5DsmYP5WA8.jpg" 
    },
    { 
        title: "【MV】アイドル衣装／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.05.28", 
        views: 441079, 
        duration: "04:14", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/a7CAn_FIfSk.jpg" 
    },
    { 
        title: "【MV】メランコリックハニー／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.04.30", 
        views: 1198044, 
        duration: "03:25", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/645TOVz8on8.jpg" 
    },
    { 
        title: "【MV】Cute for life／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.04.07", 
        views: 851000, 
        duration: "03:10", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/9tN1esSj2Tg.jpg" 
    },
    { 
        title: "【MV】東京サニーパーティー／涼海すう×葉月紗蘭×東山恵里沙【HoneyWorks】", 
        category: "music", 
        date: "2025.03.28", 
        views: 288252, 
        duration: "04:03", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/FqiedUpRQco.jpg" 
    },
    { 
        title: "【Dance Performance Video】小悪魔だってかまわない!／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2025.02.08", 
        views: 809042, 
        duration: "03:28", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/OZycs0TBwKY.jpg" 
    },
    { 
        title: "初恋の絵本（cover）／葉月紗蘭", 
        category: "music", 
        date: "2024.12.21", 
        views: 288037, 
        duration: "03:32", 
        tag: "COVER",
        thumb: "高嶺のなでしこ/img/eA307eJBSrY.jpg" 
    },
    { 
        title: "【MV】アイのウイルス／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.12.11", 
        views: 326325, 
        duration: "04:14", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/Bsq2ZKelIqQ.jpg" 
    },
    { 
        title: "【MV】I'M YOUR IDOL／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.11.27", 
        views: 1137185, 
        duration: "04:58", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/iz6KQup9Uys.jpg" 
    },
    { 
        title: "【MV】アドレナリンゲーム／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.11.06", 
        views: 485496, 
        duration: "03:05", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/n_8LTS92LXE.jpg" 
    },
    { 
        title: "【MV】死ぬまでダーリン／松本ももな【HoneyWorks】", 
        category: "music", 
        date: "2024.10.19", 
        views: 545976, 
        duration: "03:27", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/SB-r8BmlMhI.jpg" 
    },
    { 
        title: "【Dance Performance Video】LOVE ANTHEM／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.09.07", 
        views: 648607, 
        duration: "04:08", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/OItN_8OUBko.jpg" 
    },
    { 
        title: "【MV】モテチェン！／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.07.14", 
        views: 1214041, 
        duration: "03:14", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/ivfMBjNqLZE.jpg" 
    },
    { 
        title: "【MV】私より好きでいて／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.06.29", 
        views: 791194, 
        duration: "03:39", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/vo0f9AoWFLg.jpg" 
    },
    { 
        title: "【Dance Performance Video】メイド☆至上主義／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.05.11", 
        views: 1148631, 
        duration: "02:35", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/6QuCebi6vuU.jpg" 
    },
    { 
        title: "【Dance Performance Video】推しの魔法／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.03.22", 
        views: 1852847, 
        duration: "03:17", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/pDC1ZgUDLZQ.jpg" 
    },
    { 
        title: "【MV】恋を知った世界／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.02.21", 
        views: 1754623, 
        duration: "04:22", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/iSvx1zS-zSs.jpg" 
    },
    { 
        title: "【MV】私は怪物／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.02.06", 
        views: 382184, 
        duration: "03:32", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/LG3Kho_3ZOA.jpg" 
    },
    { 
        title: "【MV】可愛いって言われたい／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.02.03", 
        views: 1315484, 
        duration: "03:35", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/SLMwsq7IoWU.jpg" 
    },
    { 
        title: "【MV】美しく生きろ／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2024.01.19", 
        views: 2009754, 
        duration: "04:22", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/IPZi16uFCqs.jpg" 
    },
    { 
        title: "No.1（cover）／橋本桃呼", 
        category: "music", 
        date: "2024.01.04", 
        views: 354062, 
        duration: "04:49", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/jDQKzMDsu5A.jpg" 
    },
    { 
        title: "ラヴィ【踊ってみた】／城月菜央", 
        category: "music", 
        date: "2023.12.25", 
        views: 201980, 
        duration: "03:00", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/G3WOKUxk3TU.jpg" 
    },
    { 
        title: "【MV】いつか私がママになったら／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.12.16", 
        views: 656334, 
        duration: "04:00", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/B6ibZx9KcH4.jpg" 
    },
    { 
        title: "今好きになる。（cover）／葉月紗蘭", 
        category: "music", 
        date: "2023.10.13", 
        views: 728271, 
        duration: "04:33", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/sM9Lowgbjww.jpg" 
    },
    { 
        title: "【Dance Performance Video】17歳／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.09.03", 
        views: 1422486, 
        duration: "04:06", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/puTujCvaRjA.jpg" 
    },
    { 
        title: "【MV】すきっちゅーの！／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.09.01", 
        views: 2820534, 
        duration: "02:46", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/mBmSMIVrw1o.jpg" 
    },
    { 
        title: "17歳（cover）／葉月紗蘭×東山恵里沙", 
        category: "music", 
        date: "2023.08.28", 
        views: 357795, 
        duration: "04:25", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/w4aDnfJ_tKU.jpg" 
    },
    { 
        title: "【Dance Performance Video】月曜日の憂鬱／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.07.21", 
        views: 1880645 , 
        duration: "04:26", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/lXhue1Chv6c.jpg" 
    },
    { 
        title: "【MV】初恋のひと。／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.07.01", 
        views: 4881231 , 
        duration: "03:49", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/KgcjPd8n2Es.jpg" 
    },
    { 
        title: "【MV】決戦スピリット／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.06.22", 
        views: 1370617 , 
        duration: "04:33", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/B2S-jwfs6Ic.jpg" 
    },
    { 
        title: "【Dance Performance Video】ヒロインは平均以下。／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.06.21", 
        views: 2719467 , 
        duration: "03:47", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/8wwTjwjqQR0.jpg" 
    },
    { 
        title: "決戦スピリット（cover）／日向端ひな×籾山ひめり", 
        category: "music", 
        date: "2023.06.15", 
        views: 342687 , 
        duration: "04:47", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/aeLMEitzNgo.jpg" 
    },
    { 
        title: "【MV】革命の女王／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.04.21", 
        views: 1329734 , 
        duration: "04:04", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/WcJbDYD9u94.jpg" 
    },
    { 
        title: "【MV】僕は君になれない／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.04.21", 
        views: 1291853 , 
        duration: "04:26", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/-Mm3QGGxg1k.jpg" 
    },
    { 
        title: "【MV】男の子の目的は何？／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2023.03.20", 
        views: 3063656 , 
        duration: "02:50", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/-hTyIuPim5o.jpg" 
    },
    { 
        title: "【MV】女の子は強い／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2022.12.25", 
        views: 3265233 , 
        duration: "04:28", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/edid66UmfiQ.jpg" 
    },
    { 
        title: "【MV】可愛くてごめん（cover）／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2022.11.18", 
        views: 28718350  , 
        duration: "03:37", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/MPywGQPLJPo.jpg" 
    },
    { 
        title: "【Dance Performance Video】乙女どもよ。／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2022.10.24", 
        views: 3070929  , 
        duration: "04:10", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/JAA4CaJdXc8.jpg" 
    },
    { 
        title: "【MV】ユメムスビ／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2022.10.01", 
        views: 1634013  , 
        duration: "03:47", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/qgYnKvdcMJQ.jpg" 
    },
    { 
        title: "【MV】アンチファン／高嶺のなでしこ【HoneyWorks】", 
        category: "music", 
        date: "2022.08.27", 
        views: 1570583  , 
        duration: "04:03", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/dzOGNlmCi7Y.jpg" 
    },
    { 
        title: "【overture】We are TAKANE NO NADESHIKO／高嶺のなでしこ", 
        category: "music", 
        date: "2022.08.05", 
        views: 188367  , 
        duration: "01:05", 
        tag: "MV",
        thumb: "高嶺のなでしこ/img/YcGoOQcXjO8.jpg" 
    }
];