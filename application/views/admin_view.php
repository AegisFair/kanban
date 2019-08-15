<div class="admin-panel">
    <section class="all-users">
        <ul>
            <?php 
                for ($i=0; $i < count($data['allUsers']); $i++) { 
                    ?>
                        <li id='<?php echo $data['allUsers'][$i]['login']?>'><?php echo $data['allUsers'][$i]['login'] ?></li>
                    <?php
                }
            ?>
        </ul>
    </section>
    <section class="all-group-desks">
        <ul>
            <?php 
                for ($i=0; $i < count($data['allGroupDesks']); $i++) { 
                ?>
                    <li id='<?php echo $data['allGroupDesks'][$i]['id_desk']?>'><?php echo $data['allGroupDesks'][$i]['name_desk'] ?></li>
                <?php
                }
            ?>
        </ul>
    </section>
    <section class="current-desk">

    </section>
</div>