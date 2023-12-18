# Proces vytváření překladů

1. Otevřete nástroj Poedit (zdarma ke stažení na adrese https://poedit.net/download).
2. `Soubor->Nový` (CTRL + N)
3. Vyberte jazyk překladu ze seznamu pro vybranou zemi (např.: kanadská francouzština) a potvrďte - "OK".
4. Uložte soubor (CTRL + S) - zobrazí se dialogové okno "Uložit jako". Vložte textový název domény s kódem jazyka a země (např.: `woosms-sms-module-for-woocommerce-fr_CA.po`). Vyberte adresář v rámci vašeho projektu, kam má být soubor uložen (`/languages`)
5. Uložte
6. V PhpStormu přejděte do adresáře s překlady (`/languages`) a zkopírujte obsah libovolného již přeloženého `.po` souboru i s hlavičkou.
7. Přejděte do ChatGPT
8. V ChatGPT nechte přeložit obsah `.po` souboru (s vyplněnými `msgid "Original text"`) do vybraného jazyka. Přeložené texty se doplní do `msgstr "Přeložený text"`.
9. Zkopírujte výstup a vložte jej do souboru vybraného jazyka (`woosms-sms-module-for-woocommerce-fr_CA.po`) v adresáři `/languages`. Poeditem automaticky vygenerovaná hlavička je nahrazena. 
10. Přepněte se zpět do Poeditu, kde se objeví nové překlady. Překlady zkontrolujte, popřípadě upravte.
11. Uložte (pokud máte v `Soubor->Nastavení->Obecné->Editace` zaškrtnutou volbu "Při uložení automaticky zkompilovat MO soubor" vytvoří se vám ve stejném adresáři nový zkompilovaný `woosms-sms-module-for-woocommerce-fr_CA.mo` soubor)
12. 