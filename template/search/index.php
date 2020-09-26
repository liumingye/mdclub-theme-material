<?php include dirname(__FILE__) . "/../functions.php"; ?>
<?php
$user_id = get_user_id();

$include = ['user', 'topics'];

$text = get_route()->getArgument('text');
$page = get_route()->getArgument('ppp', 1);

// 搜索页面 lmy
$articles = get_articles([
    'title' => htmlspecialchars($text),
    'include' => $include,
    'page' => $page,
    'per_page' => 20,
    'order' => '-update_time',
]);

$meta_title = '临时搜索页面';
?>
<?php include dirname(__FILE__) . "/../public/header.php"; ?>
<style>
.my-ppp {
  font-size: 14px;
  overflow: hidden;
  margin: 0 auto;
  text-align: center;
  color: #333333;
  display: inline-block;
}

.my-ppp .my-ppp-a,
.my-ppp .my-ppp-b {
  float: left;
  width: 40px;
  height: 30px;
  line-height: 30px;
  text-align: center;
  border: 1px solid #ddd;
  cursor: pointer;
}

.my-ppp .my-ppp-a.my-ppp-forbid,
.my-ppp .my-ppp-b.my-ppp-forbid {
  pointer-events: none;
  background-color: rgba(0, 0, 0, 0.1);
  color: rgba(0, 0, 0, 0.2);
}

.my-ppp .my-ppp-a:not(.my-ppp-forbid):hover,
.my-ppp .my-ppp-b:not(.my-ppp-forbid):hover {
  border-color: #a02d35;
}

.my-ppp .my-ppp-group {
  float: left;
  margin: 0;
  padding: 0;
  overflow: hidden;
}

.my-ppp .my-ppp-group li {
  float: left;
  list-style: none;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  min-width: 30px;
  padding: 0 8px;
  height: 30px;
  line-height: 30px;
  text-align: center;
  margin: 0 5px;
  cursor: pointer;
}

.my-ppp .my-ppp-group .my-ppp-cell {
  border: 1px solid #ddd;
  border-radius: 2px;
}

.my-ppp .my-ppp-group .my-ppp-cell:hover,
.my-ppp .my-ppp-group .my-ppp-checked {
  border-color: #a02d35;
  background-color: #a02d35;
  color: #ffffff;
}

.my-ppp .my-ppp-group .my-ppp-omit {
  pointer-events: none;
}
</style>
<?php if (!$user_id): ?>
<div class="mdui-container">
  <div>请先登录</div>
</div>
<?php else: ?>
<?php
if(count($articles['data']) == 0){
    echo '没找到！';
}
?>
<div class="mdui-container">
  <div>
    <?php foreach ($articles['data'] as $article): ?>
        <a class="mc-list-item" href="<?= get_root_url() ?>/articles/<?= $article['article_id'] ?>">
        <div class="mc-user-popover">
            <div class="avatar user-popover-trigger" style="background-image: url(<?= $article['relationships']['user']['avatar']['middle'] ?>);"></div>
        </div>
        <div class="title mdui-text-color-theme-text"><?= $article['title'] ?> <?= date('Y-m-d H:i:s', $article['create_time']) ?></div>
        </a>
    <?php endforeach; ?>
  </div>
  <div class="my-ppp"></div>
</div>
<?php endif; ?>
</div>
<script>
function myPPPInit({
    ppps = 1,
    currentPPP = 1,
    element = '.my-ppp',
    callback
}) {

    intercept();

    const myPPPEl = document.querySelector(element);

    // 构造结构
    let htmlStrArr = [];
    for (let i = 0; i < ppps; i++) {
        htmlStrArr.push(`<li class="my-ppp-cell">${i + 1}</li>`);
    };
    if (ppps > 7) {
        htmlStrArr.splice(5, htmlStrArr.length - 6, "<li class='my-ppp-omit'>...</li>");
    };
    htmlStr = htmlStrArr.join("");
    let pppHtmlStr = `<div class="my-ppp-a"><</div>
        <ul class="my-ppp-group">${htmlStr}</ul>
        <div class="my-ppp-b">></div>`;

    // 注入结构
    myPPPEl.innerHTML = pppHtmlStr;

    // 标记默认页
    clickPPPFun(currentPPP);

    // 上下页切换事件注册
    let btns = document.querySelectorAll(`${element} div`);
    btns.forEach(el => {
        el.onclick = switchPPP;
    });

    // 点击事件注册
    myPPPEl.onclick = function (e) {
        // console.log(e)
        let classNameArr = e.target.className.split(" ");
        if (classNameArr.indexOf("my-ppp-cell") !== -1) {
            clickPPPFun(Number(e.target.innerText));
        };
    }

    // 上下页按钮触发
    function switchPPP(e) {
        // 获取当前页
        let ppp = document.querySelector(`${element} .my-ppp-checked`).innerText - 0;

        let classNameArr = e.target.className.split(" ");
        if (classNameArr.indexOf("my-ppp-a") !== -1) {
            clickPPPFun(ppp - 1); // 上一页
        } else if (classNameArr.indexOf("my-ppp-b") !== -1) {
            clickPPPFun(ppp + 1); // 下一页
        };
    };


    // 分页切换处理
    function clickPPPFun(ppp) {
        ppp = Number(ppp);
        // 满足条件改变结构
        if (ppps > 7) {
            let newEl = '';
            if (ppp <= 4) {
                newEl = `
                <li class="my-ppp-cell">1</li>
                <li class="my-ppp-cell">2</li>
                <li class="my-ppp-cell">3</li>
                <li class="my-ppp-cell">4</li>
                <li class="my-ppp-cell">5</li>
                <li class="my-ppp-omit">...</li>
                <li class="my-ppp-cell">${ppps}</li>`;
            } else if (ppp >= 5 && ppp < ppps - 3) {
                newEl = `
                <li class="my-ppp-cell">1</li>
                <li class="my-ppp-omit">...</li>
                <li class="my-ppp-cell">${ppp - 1}</li>
                <li class="my-ppp-cell">${ppp}</li>
                <li class="my-ppp-cell">${ppp + 1}</li>
                <li class="my-ppp-omit">...</li>
                <li class="my-ppp-cell">${ppps}</li>`;
            } else if (ppp >= ppps - 3) {
                newEl = `
                <li class="my-ppp-cell">1</li>
                <li class="my-ppp-omit">...</li>
                <li class="my-ppp-cell">${ppps - 4}</li>
                <li class="my-ppp-cell">${ppps - 3}</li>
                <li class="my-ppp-cell">${ppps - 2}</li>
                <li class="my-ppp-cell">${ppps - 1}</li>
                <li class="my-ppp-cell">${ppps}</li>`;
            };
            document.querySelector(`${element} .my-ppp-group`).innerHTML = newEl;
        };

        // 标注选中项
        let pppCellELs = document.querySelectorAll(`${element} .my-ppp-cell`);
        pppCellELs.forEach(el => {
            if (el.innerText == ppp) {
                el.classList.add('my-ppp-checked');
            } else {
                el.classList.remove('my-ppp-checked');
            };
        });

        forbidden(ppp);

        // 回调响应
        callback && callback(ppp);
    };

    // 上下页按钮启禁
    function forbidden(ppp) {
        let prveEl = document.querySelector(`${element} .my-ppp-a`);
        let nextEl = document.querySelector(`${element} .my-ppp-b`);
        if (ppp === 1) {
            prveEl.classList.add('my-ppp-forbid');
        } else {
            prveEl.classList.remove('my-ppp-forbid');
        };

        if (ppp === ppps) {
            nextEl.classList.add('my-ppp-forbid');
        } else {
            nextEl.classList.remove('my-ppp-forbid');
        };
    };

    // 参数检验
    function intercept() {
        if (!ppps || ppps === 0 || (Math.floor(ppps) != ppps)) {
            throw "my-ppp中ppps必须是整数且不为0";
            ppps = Math.floor(ppps);
        };

        if (!currentPPP || currentPPP === 0 || (Math.floor(currentPPP) !== currentPPP)) {
            throw "my-ppp中currentPPP必须是整数且不为0";
            currentPPP = Math.floor(currentPPP);

        };

        if (document.querySelectorAll(element).length === 0) {
            throw element + "元素不存在";
        };

        if (currentPPP > ppps) {
            throw "当前页不存在";
        }
    };
}

myPPPInit({
    ppps: <?php echo $articles['pagination']['pages'] ?>,
    currentPPP: <?php echo $page; ?>,
    element: '.my-ppp',
    callback: function (ppp) {
        if(ppp != <?php echo $page; ?>){
            location.href = '<?= get_root_url() ?>/search/<?php echo $text; ?>/ppp/' + ppp;
        }
    }
});
</script>
</body>
</html>