<hr>
<article class="comment">
    <footer>
        <img src="<?php echo $actorImage; ?>?sz=32" style="float:left">
        <small>By <strong><a href="<?php echo $actorUrl; ?>"><?php echo $actorName; ?></a></strong></small>
        <small>Posted at <?php echo date("H:i (d.m.Y)", $published); ?></small>
    </footer>
    
    <p><?php echo $comment; ?></p>
</article>