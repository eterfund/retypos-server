<div class="left">
    <div id="menu">
        <span class="heading">Меню</span>
        <div class="items">
            <ul>
                <?php  
                    if  ($items)  {
                        foreach ($items as $item)  {
                            echo "<li>".$item."</li>";
                        }
                    }
                ?>
            </ul>
        </div>
    </div>
</div>