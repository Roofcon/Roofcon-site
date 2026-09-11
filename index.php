<?php
session_start();
$dataFile=__DIR__.'/../data/content.json';
$uploadDir=__DIR__.'/../uploads/';
if(!isset($_SESSION['roofcon_admin'])) {
  if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password'])) {
    // Change this password immediately after first deployment.
    if(hash_equals('ROOFCON-ADMIN-2026', $_POST['password'])) $_SESSION['roofcon_admin']=true;
    else $error='Mot de passe incorrect.';
  }
  if(!isset($_SESSION['roofcon_admin'])) { ?>
  <!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ROOFCON — Administration</title>
  <style>body{font-family:Arial;background:#211f20;display:grid;place-items:center;min-height:100vh;margin:0}.box{background:#fff;padding:38px;width:min(390px,85%)}h1{margin:0 0 20px}.box input{width:100%;padding:13px;margin:8px 0 14px;box-sizing:border-box}.box button{background:#a27b5b;color:#fff;border:0;padding:13px 20px;font-weight:bold;width:100%}.err{color:#a00}</style></head><body><form class="box" method="post"><h1>ROOFCON</h1><p>Administration du site</p><?php if(isset($error))echo '<p class="err">'.htmlspecialchars($error).'</p>'; ?><input type="password" name="password" placeholder="Mot de passe" autofocus><button>Se connecter</button></form></body></html><?php exit; }
}
$data=json_decode(file_get_contents($dataFile),true);
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
  if($_POST['action']==='save') {
    $data['phone']=$_POST['phone']??$data['phone']; $data['email']=$_POST['email']??$data['email'];
    foreach(['hero_kicker','hero_title_fr','hero_title_de','hero_text_fr','hero_text_de','about_title_fr','about_title_de','about_text1_fr','about_text1_de','about_text2_fr','about_text2_de'] as $k) $data[$k]=$_POST[$k]??$data[$k];
    foreach($data['services'] as $i=>&$s) foreach(['number','title_fr','title_de','text_fr','text_de'] as $k) $s[$k]=$_POST["s_{$i}_{$k}"]??$s[$k]; unset($s);
    foreach($data['process'] as $i=>&$p) foreach(['title_fr','title_de','text_fr','text_de'] as $k) $p[$k]=$_POST["p_{$i}_{$k}"]??$p[$k]; unset($p);
    foreach($data['facts'] as $i=>&$f) foreach(['value','label_fr','label_de'] as $k) $f[$k]=$_POST["f_{$i}_{$k}"]??$f[$k]; unset($f);
    foreach($data['references'] as $i=>&$r) { $r['title_fr']=$_POST["r_{$i}_title_fr"]??$r['title_fr']; $r['title_de']=$_POST["r_{$i}_title_de"]??$r['title_de']; }
    file_put_contents($dataFile,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX);
    $msg='Modifications enregistrées.';
  }
  if($_POST['action']==='upload') {
    $i=(int)$_POST['ref'];
    if(isset($_FILES['image']) && $_FILES['image']['error']===UPLOAD_ERR_OK) {
      $f=$_FILES['image']; $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
      if(in_array($ext,['jpg','jpeg','png','webp'],true) && $f['size']<8*1024*1024) {
        $name='ref_'.($i+1).'_'.time().'.'.$ext; move_uploaded_file($f['tmp_name'],$uploadDir.$name); $data['references'][$i]['image']='uploads/'.$name;
        file_put_contents($dataFile,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX); $msg='Photo mise à jour.';
      } else $msg='Photo refusée : JPG, PNG ou WebP, 8 Mo maximum.';
    }
  }
}
function h($v){return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8');}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ROOFCON — Administration</title>
<style>
body{font-family:Arial;margin:0;background:#f5f2ee;color:#211f20}.top{background:#211f20;color:#fff;padding:18px 4%;display:flex;justify-content:space-between;align-items:center}.top a{color:#fff}.wrap{max-width:1100px;margin:30px auto;padding:0 20px}.card{background:#fff;padding:25px;margin:18px 0;border:1px solid #e5dfda}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.field{margin:10px 0}.field label{display:block;font-size:12px;font-weight:bold;margin-bottom:6px}.field input,.field textarea{width:100%;padding:10px;border:1px solid #ccc;box-sizing:border-box;font:inherit}.field textarea{min-height:85px}.full{grid-column:1/-1}.btn{background:#a27b5b;color:#fff;border:0;padding:12px 20px;font-weight:bold;cursor:pointer}.msg{background:#e9f5e9;padding:12px;margin:15px 0}.refbox{border-top:1px solid #ddd;padding-top:15px;margin-top:15px}.preview{max-width:220px;max-height:120px;object-fit:cover;display:block;margin:8px 0}@media(max-width:700px){.grid{grid-template-columns:1fr}.full{grid-column:auto}}
</style></head><body>
<div class="top"><strong>ROOFCON — ADMINISTRATION</strong><a href="../" target="_blank">Voir le site ↗</a></div>
<div class="wrap"><h1>Modifier le site</h1><?php if($msg)echo '<div class="msg">'.h($msg).'</div>'; ?>
<form method="post"><input type="hidden" name="action" value="save">
<div class="card"><h2>Coordonnées</h2><div class="grid">
<div class="field"><label>Téléphone</label><input name="phone" value="<?=h($data['phone'])?>"></div>
<div class="field"><label>E-mail</label><input name="email" value="<?=h($data['email'])?>"></div></div></div>

<div class="card"><h2>Accueil</h2><div class="grid">
<div class="field"><label>Accroche</label><input name="hero_kicker" value="<?=h($data['hero_kicker'])?>"></div>
<div class="field"></div>
<div class="field"><label>Titre FR</label><input name="hero_title_fr" value="<?=h($data['hero_title_fr'])?>"></div>
<div class="field"><label>Titre DE</label><input name="hero_title_de" value="<?=h($data['hero_title_de'])?>"></div>
<div class="field"><label>Texte FR</label><textarea name="hero_text_fr"><?=h($data['hero_text_fr'])?></textarea></div>
<div class="field"><label>Texte DE</label><textarea name="hero_text_de"><?=h($data['hero_text_de'])?></textarea></div></div></div>

<div class="card"><h2>Services</h2><?php foreach($data['services'] as $i=>$s): ?><div class="refbox"><h3>Service <?=($i+1)?></h3><div class="grid">
<div class="field"><label>Numéro / catégorie</label><input name="s_<?=$i?>_number" value="<?=h($s['number'])?>"></div>
<div></div><div class="field"><label>Titre FR</label><input name="s_<?=$i?>_title_fr" value="<?=h($s['title_fr'])?>"></div><div class="field"><label>Titre DE</label><input name="s_<?=$i?>_title_de" value="<?=h($s['title_de'])?>"></div>
<div class="field"><label>Description FR</label><textarea name="s_<?=$i?>_text_fr"><?=h($s['text_fr'])?></textarea></div><div class="field"><label>Description DE</label><textarea name="s_<?=$i?>_text_de"><?=h($s['text_de'])?></textarea></div></div></div><?php endforeach; ?></div>

<div class="card"><h2>Notre méthode</h2><?php foreach($data['process'] as $i=>$p): ?><div class="refbox"><h3>Étape <?=($i+1)?></h3><div class="grid"><div class="field"><label>Titre FR</label><input name="p_<?=$i?>_title_fr" value="<?=h($p['title_fr'])?>"></div><div class="field"><label>Titre DE</label><input name="p_<?=$i?>_title_de" value="<?=h($p['title_de'])?>"></div><div class="field"><label>Texte FR</label><textarea name="p_<?=$i?>_text_fr"><?=h($p['text_fr'])?></textarea></div><div class="field"><label>Texte DE</label><textarea name="p_<?=$i?>_text_de"><?=h($p['text_de'])?></textarea></div></div></div><?php endforeach; ?></div>

<div class="card"><h2>L’entreprise</h2><div class="grid">
<div class="field"><label>Titre FR</label><input name="about_title_fr" value="<?=h($data['about_title_fr'])?>"></div><div class="field"><label>Titre DE</label><input name="about_title_de" value="<?=h($data['about_title_de'])?>"></div>
<div class="field"><label>Texte 1 FR</label><textarea name="about_text1_fr"><?=h($data['about_text1_fr'])?></textarea></div><div class="field"><label>Texte 1 DE</label><textarea name="about_text1_de"><?=h($data['about_text1_de'])?></textarea></div>
<div class="field"><label>Texte 2 FR</label><textarea name="about_text2_fr"><?=h($data['about_text2_fr'])?></textarea></div><div class="field"><label>Texte 2 DE</label><textarea name="about_text2_de"><?=h($data['about_text2_de'])?></textarea></div>
</div><?php foreach($data['facts'] as $i=>$f): ?><div class="refbox"><h3>Chiffre <?=($i+1)?></h3><div class="grid"><div class="field"><label>Valeur</label><input name="f_<?=$i?>_value" value="<?=h($f['value'])?>"></div><div class="field"><label>Libellé FR</label><input name="f_<?=$i?>_label_fr" value="<?=h($f['label_fr'])?>"></div><div class="field"><label>Libellé DE</label><input name="f_<?=$i?>_label_de" value="<?=h($f['label_de'])?>"></div></div></div><?php endforeach; ?></div>

<div class="card"><h2>Références / réalisations</h2><?php foreach($data['references'] as $i=>$r): ?><div class="refbox"><h3>Référence <?=($i+1)?></h3><div class="grid">
<div class="field"><label>Nom FR</label><input name="r_<?=$i?>_title_fr" value="<?=h($r['title_fr'])?>"></div><div class="field"><label>Nom DE</label><input name="r_<?=$i?>_title_de" value="<?=h($r['title_de'])?>"></div>
<div class="field full"><?php if($r['image']): ?><img class="preview" src="../<?=h($r['image'])?>"><?php endif; ?><label>Remplacer la photo</label></div></div></div><?php endforeach; ?></div>
<button class="btn">Enregistrer toutes les modifications</button></form>

<?php foreach($data['references'] as $i=>$r): ?><div class="card"><h3>Photo — Référence <?=($i+1)?></h3><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload"><input type="hidden" name="ref" value="<?=$i?>"><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required> <button class="btn">Téléverser</button></form></div><?php endforeach; ?>

<p style="font-size:12px;color:#706b67">Le mot de passe de démonstration est <strong>ROOFCON-ADMIN-2026</strong>. Change-le dans <code>admin/index.php</code> avant la mise en ligne.</p>
</div></body></html>