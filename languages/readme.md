# Proces vytváření překladů

Podle potřeby můžete kombinovat oba způsoby vytváření překladů.  

Zdroje:

- https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/
- https://codex.wordpress.org/I18n_for_WordPress_Developers
- https://www.gnu.org/software/gettext/manual/html_node/

## Překlad pomocí skriptů - automatizace
1. **Získání překladů** - Obalte všechny fráze, které chcete přeložit do některé z následujících překladových funkcí: `__()`, `esc_html__()`, `esc_attr__()`, `_e()`, `esc_html_e()`, `esc_attr_e()` [viz dokumentace](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/#localization-functions). Načtěte ze zdrojového kódu fráze pomocí příkazu: `composer run translates:extract`, který uloží vyextrahované fráze do šablony `template.pot` v adresáři `/languages` do pole `msgid ""`. Pole `msgstring ""` zůstane prázdné. Tento POT soubor (soubor s příponou `.pot`; Portable Object Template) se předá překladateli na překlad nebo se v našem případě přeloží pomocí ChatGPT do daného jazyka (zkopírujeme obsah souboru a necháme ChatGPT, aby naplnil pole `msgstr ""` překlady pro námi zvolený jazyk. Soubor uložíme jako PO soubor (soubor s příponou `.po`; Portable Object) do adresáře `/languages`. Tento PO soubor musí mít název pluginu a kód jazyka pro danou lokalizaci (např.: `woosms-sms-module-for-woocommerce-cs.po` nebo `woosms-sms-module-for-woocommerce-cs_CZ.po` pro češtinu) [viz dokumentace](https://developer.wordpress.org/plugins/internationalization/localization/#using-localizations).
2. **Aktualizace překladů** - Pokud potřebujeme přidat do pluginu nové fráze, provedeme krok číslo 1. Pak pomocí příkazu `composer run translates:update` vložíme nové fráze z aktualizovaného POT souboru do existujících PO souborů. Ty jsou samozřejmě nepřeložené a je potřeba je přeložit.
3. **Kompilace překladů** - Aby mohli být překlady v pluginu použity musí se PO soubory zkompilovat do MO souborů (soubory s příponou `.mo`; Machine Objects). To provedeme příkazem `composer run translates:compile`. Ten je vytvoří v adresáři `/languages`. Tyto soubory v gitu netrackujeme, slouží k testování překladů v lokálním prostředí. 
4. **Vložení nového jazyka** - Nejjednodušší způsob jak vytvořit překlad do nového jazyka je zkopírovat kterýkoli, pro jiný jazyk vytvořený `.po` soubor, změnit jeho název - kód země (první část názvu `woosms-sms-module-for-woocommerce` je povinná), údaje v hlavičce (`Language:` a `Plural forms:`) a nahraďit všechna pole `msgstring` novými překlady. Nebo použijte šablonu `template.pot` a spusťte příkaz `msginit --locale=fr_CA --input=languages/template.pot --output=languages/woosms-sms-module-for-woocommerce-fr_CA.po`, který inicializuje metainformace v hlavičce hodnotami z uživatelského prostředí (např.: do hlavičky automaticky doplní hodnotu `Plural forms:`, ale je třeba upravit `Content-type:` charset=UTF-8)

## Překlad pomocí programu Poedit - manuální překlad
### Vložení nového jazyka
1. Otevřete nástroj Poedit (zdarma ke stažení na adrese https://poedit.net/download).
2. `Soubor->Nový` (`CTRL + N`)
3. Vyberte jazyk překladu ze seznamu pro vybranou zemi (např.: kanadská francouzština) a potvrďte - `OK`.
4. Uložte soubor (`CTRL + S`) - zobrazí se dialogové okno `Uložit jako`. Vložte textový název domény s kódem jazyka a země (např.: `woosms-sms-module-for-woocommerce-fr_CA.po`). Vyberte adresář v rámci vašeho projektu, kam má být soubor uložen (`/languages`)
5. Uložte
6. V PhpStormu přejděte do adresáře s překlady (`/languages`) a zkopírujte obsah libovolného již přeloženého `.po` souboru i s hlavičkou.
7. Přejděte do ChatGPT a vložte
8. V ChatGPT nechte přeložit obsah `.po` souboru (s vyplněnými `msgid "Original text"`) do vybraného jazyka. Přeložené texty se doplní do `msgstr "Přeložený text"`.
9. Zkopírujte výstup a vložte jej do souboru vybraného jazyka (`woosms-sms-module-for-woocommerce-fr_CA.po`) v adresáři `/languages`. Poeditem automaticky vygenerovaná hlavička je nahrazena. 
10. Přepněte se zpět do Poeditu, kde se objeví nové překlady. Překlady zkontrolujte, popřípadě upravte.
11. Uložte (pokud máte v `Soubor->Nastavení->Obecné->Editace` zaškrtnutou volbu `Při uložení automaticky zkompilovat MO soubor` vytvoří se vám ve stejném adresáři nový zkompilovaný `woosms-sms-module-for-woocommerce-fr_CA.mo` soubor)
12. Pokud nechcete `.mo` soubory ukládat (tyto soubory v gitu netrackujeme, slouží například pro testováni na localhostu) zrušte zaškrtnutí této volby.
13. Při instalaci pluginu se .po soubory automaticky zkompilují do .mo formátu (release.yml) - GitHub Actions

### Doplnění nové fráze do již existujícího jazyka
1. Ve zdrojovém kódu souboru (například Meta.php) vložte novou frázi v angličtině (EN slouží jako default v případě, že nemáme překlad do daného jazyka) do překladové funkce podle použití -  např.: `esc_html__('New translation')`  (dokumentace funkcí zde - https://developer.wordpress.org/reference/functions/)
2. Otevřete program Poedit a otevřete soubor s překladem v jazyce, který chcete překládat (např.: `woosms-sms-module-for-woocommerce-fr_CA.po`).
3. Dejte `Překlad->Aktualizovat za zdrojového kódu` nebo klikněte na tlačítko `Aktualizovat z kódu`. V dialogovém okně `Výsledek aktualizace` potvrďte vložení nového řetězce.
4. Přeložte nebo uložte a dejte na překlad.
5. Uložte soubor.