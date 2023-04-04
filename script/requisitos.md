/ instalar PHP /
sudo apt install php

/ instalar .tar /
sudo apt install tar



/ roda o arquivo a cada 6horas 
crontab -e 

0 */6 * * * php /athenas/script/scriptbkp.php
