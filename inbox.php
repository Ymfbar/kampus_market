<?php
session_start();
include 'includes/config.php';
include 'includes/navbar.php';
include 'includes/footer.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$user_id = $_SESSION['user']['id'];

/* ================= AJAX LOAD CHAT ================= */
if (isset($_GET['ajax']) && isset($_GET['user'])) {
    $other_id = (int)$_GET['user'];
    $conn->query("UPDATE messages SET is_read = 1 WHERE sender_id = $other_id AND receiver_id = $user_id");

    $stmt = $conn->prepare("
        SELECT * FROM messages
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("iiii", $user_id, $other_id, $other_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()):
        $time = date('H:i', strtotime($row['created_at']));
?>
<div class="message-row <?= $row['sender_id']==$user_id?'me':'other' ?>">
    <div class="bubble <?= $row['sender_id']==$user_id?'me':'other' ?>">
        <?= htmlspecialchars($row['pesan']) ?>
        <div class="msg-time"><?= $time ?></div>
    </div>
</div>
<?php endwhile; exit; }

/* ================= AJAX SEND ================= */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ajax'])) {
    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, receiver_id, pesan, is_read)
        VALUES (?, ?, ?, 0)
    ");
    $stmt->bind_param("iis", $_SESSION['user']['id'], $_POST['to'], $_POST['pesan']);
    $stmt->execute();
    exit('ok');
}

/* ================= CHAT LIST ================= */
$listQuery = "
SELECT 
    IF(sender_id = ?, receiver_id, sender_id) AS other_id,
    (SELECT nama FROM users WHERE id = other_id) AS other_name,
    MAX(created_at) AS last_time,
    (SELECT pesan FROM messages 
        WHERE (sender_id = other_id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = other_id)
        ORDER BY created_at DESC LIMIT 1
    ) AS last_message,
    (SELECT COUNT(*) FROM messages WHERE sender_id = other_id AND receiver_id = ? AND is_read = 0) AS unread_count
FROM messages
WHERE sender_id = ? OR receiver_id = ?
GROUP BY other_id
ORDER BY last_time DESC
";
$stmt = $conn->prepare($listQuery);
$stmt->bind_param("iiiiii",$user_id,$user_id,$user_id,$user_id,$user_id,$user_id);
$stmt->execute();
$chatList = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Preppy Finds</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* RESET */
*{box-sizing:border-box;margin:0;padding:0;}
body, html{height:100%; font-family:'Segoe UI',sans-serif; background:#121212; color:#fff; overflow:hidden;}

/* FULLSCREEN FLEX */
/* BODY SEBAGAI FLEX CONTAINER */
body, html {
    height: 100%;
    margin: 0;
    display: flex;
    justify-content: center; /* center horizontal */
    align-items: center;     /* center vertical */
    background: #121212;
    font-family: 'Segoe UI', sans-serif;
    color: #fff;
}

/* WRAPPER FIXED WIDTH ATAU RESPONSIVE */
.wrapper {
    display: flex;
    width: calc(100vw - 70px); /* jika mau geser sesuai navbar */
    max-width: 1200px;         /* optional biar tidak terlalu lebar di layar besar */
    height: 90vh;              /* biar ada margin atas-bawah */
    background: #181818;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}


.sidebar {
    width: 280px; /* tetap lebar sidebar */
    min-width: 250px;
    background: #1e1e1e;
    overflow-y: auto;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #333;
}

.sidebar-header{
    padding:20px;
    font-weight:600;
    font-size:18px;
    border-bottom:1px solid #333;
}
.chat-item{
    padding:12px 16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    cursor:pointer;
    transition:0.2s;
    border-radius:10px;
    margin:4px 8px;
}
.chat-item:hover{background:#2a2a2a;}
.chat-item.active{background:#333;}
.chat-item strong{font-size:15px; font-weight:500;}
.chat-item .small{color:#aaa; font-size:13px;}
.chat-item .badge{font-size:12px; background:#405de6; color:#fff;}

/* CHAT AREA */
.chat-area{
    flex:1;
    display:flex;
    flex-direction:column;
    position:relative;
    background:#181818;
    overflow:hidden;
}

/* HEADER */
.chat-header{
    padding:16px 20px;
    border-bottom:1px solid #333;
    font-weight:600;
    font-size:17px;
    background:#1f1f1f;
}

/* MESSAGES */
.chat-messages{
    flex:1;
    padding:20px;
    overflow-y:auto;
    display:flex;
    flex-direction:column;
    gap:12px;
    scrollbar-width: thin;
    scrollbar-color: #555 #1e1e1e;
}

/* MESSAGE BUBBLES COMPACT */
.message-row {
    display: flex;
    width: 100%;
    margin: 0;
}
.message-row.me {
    justify-content: flex-end;
}
.message-row.other {
    justify-content: flex-start;
}
.bubble {
    max-width:55%;           /* lebih ramping */
    padding:4px 10px;        /* lebih tipis */
    border-radius:18px;      /* bulat tapi kompak */
    font-size:13px;          /* sedikit lebih kecil */
    line-height:1.1;         /* lebih rapat */
    position: relative;
    word-break: break-word;
    white-space: pre-wrap;
    min-height: 20px;        /* supaya tidak terlalu tinggi */
}
.bubble.me {
    background: linear-gradient(135deg,#405de6,#833ab4);
    color:#fff;
    border-bottom-right-radius:6px;
}
.bubble.other {
    background:#2a2a2a;
    color:#fff;
    border-bottom-left-radius:6px;
}
.msg-time {
    font-size:11px;           /* lebih kecil */
    color: rgba(255,255,255,0.5);
    text-align: right;
    margin-top:2px;
}
.bubble.other .msg-time {
    color: rgba(255,255,255,0.4);
}


/* CHAT INPUT */
.chat-input{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 16px;
    border-top:1px solid #333;
    background:#1f1f1f;
}
.chat-input input{
    flex:1;
    border-radius:999px;
    border:1px solid #333;
    background:#121212;
    color:#fff;
    padding:10px 16px;
}
.chat-input input::placeholder{color:#aaa;}
.chat-input button{
    border-radius:999px;
    background:#405de6;
    color:#fff;
    padding:8px 20px;
    border:none;
    cursor:pointer;
}
.chat-input button:hover{opacity:0.9;}

/* SCROLLBAR DARK */
::-webkit-scrollbar{
    width:8px;
}
::-webkit-scrollbar-track{
    background:#1e1e1e;
}
::-webkit-scrollbar-thumb{
    background:#555;
    border-radius:4px;
}

/* RESPONSIVE */
@media(max-width:768px){
    .sidebar{width:200px; min-width:150px;}
    .chat-messages{padding:10px;}
    .bubble{max-width:90%;}
}
</style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">Messages</div>
        <?php while($c=$chatList->fetch_assoc()): ?>
        <div class="chat-item" onclick="openChat(<?= $c['other_id'] ?>, this)">
            <div class="flex-grow-1">
                <strong><?= htmlspecialchars($c['other_name']) ?></strong>
                <div class="small text-truncate" style="max-width:200px">
                    <?= htmlspecialchars($c['last_message']) ?>
                </div>
            </div>
            <?php if($c['unread_count']>0): ?>
                <span class="badge"><?= $c['unread_count'] ?></span>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- CHAT AREA -->
    <div class="chat-area">
        <div class="chat-header" id="chatHeader">Pilih chat</div>
        <div class="chat-messages text-center text-muted justify-content-center" id="chatBox">
            Pilih chat untuk mulai percakapan
        </div>
        <form id="chatForm" class="chat-input d-none" onsubmit="sendMsg(event)">
            <input type="text" id="msgInput" placeholder="Tulis pesan..." required>
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<script>
let activeUser=null;
const chatBox=document.getElementById('chatBox');
const chatHeader=document.getElementById('chatHeader');
const chatForm=document.getElementById('chatForm');
const msgInput=document.getElementById('msgInput');

function openChat(id, el){
    activeUser=id;
    document.querySelectorAll('.chat-item').forEach(i=>i.classList.remove('active'));
    el.classList.add('active');

    fetch(`inbox.php?ajax=1&user=${id}`)
    .then(r=>r.text())
    .then(html=>{
        chatBox.classList.remove('text-center','text-muted','justify-content-center');
        chatHeader.innerText = el.querySelector('strong').innerText;
        chatBox.innerHTML = html;
        chatForm.classList.remove('d-none');
        scrollBottom();
        el.querySelector('.badge')?.remove();
    });
}

function sendMsg(e){
    e.preventDefault();
    if(!activeUser) return;
    fetch('inbox.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`ajax=1&to=${activeUser}&pesan=${encodeURIComponent(msgInput.value)}`
    }).then(()=>{
        openChat(activeUser, document.querySelector('.chat-item.active'));
        msgInput.value='';
    });
}

function scrollBottom(){
    chatBox.scrollTop = chatBox.scrollHeight;
}
</script>
</body>
</html>
