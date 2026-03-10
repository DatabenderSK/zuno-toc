=== Zuno TOC – Table of Contents ===
Contributors: martinpavlic
Tags: table of contents, toc, headings, obsah, gutenberg, zuno
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gutenberg blok pre automatický obsah článku s live náhľadom, farebným prispôsobením a auto-insertom.

== Description ==

Zuno TOC je WordPress plugin, ktorý automaticky generuje obsah článku (Table of Contents) z nadpisov H2/H3/H4.

= Hlavné funkcie =

* **Gutenberg blok** – natívny blok "Zuno TOC (Obsah článku)" s live náhľadom
* **Auto-insert** – automatické vloženie TOC pred prvý nadpis
* **3 vizuálne štýly** – Minimálny, Zaoblený, Tmavý
* **Farebné presety** – 8 predvolených farieb + vlastná hex farba
* **Odrážky alebo číslovanie** – prepínateľné per-blok
* **Skryť/Zobraziť toggle** – zložiteľný obsah s plynulou animáciou
* **Predvolene skrytý** – možnosť štartu so zloženým obsahom
* **Inline editovanie** – zmena textu aj anchor URL priamo v editore
* **Skrývanie nadpisov** – individuálne alebo hromadné skrytie H3/H4
* **Strip čísiel** – odstránenie "1.", "2." prefixov z nadpisov
* **Smooth scroll** – plynulé scrollovanie k nadpisom
* **Automatické aktualizácie** – cez GitHub releases

= Nastavenia =

* Globálne: Zuno → Zuno TOC
* Per-blok: Sidebar pri editovaní bloku
* Per-článok: Panel "Zuno TOC" v dokumente

== Installation ==

1. Stiahnite ZIP z GitHub releases
2. WordPress admin → Pluginy → Nahrať plugin → vyberte ZIP
3. Aktivujte plugin
4. Plugin automaticky vloží TOC do článkov s 3+ nadpismi

Aktualizácie sa zobrazia priamo v WordPress dashboarde.

== Frequently Asked Questions ==

= Ako zmením farbu TOC? =

Kliknite na TOC blok v editore → Sidebar → Nastavenia TOC → Farba. Alebo globálne: Zuno → Zuno TOC → Farba akcentu.

= Ako skryjem konkrétny nadpis z TOC? =

Kliknite na ikonu oka vedľa nadpisu v editore, alebo použite checkbox v Sidebar → Nadpisy.

= Ako vypnem TOC pre jeden článok? =

Panel "Zuno TOC" v pravom sidebar dokumentu → zaškrtnite "Vypnúť TOC pre tento článok".

= Podporuje plugin H4 nadpisy? =

Áno. V Zuno → Zuno TOC → Úrovne nadpisov zaškrtnite H4.

== Changelog ==

= 1.0.6 =
* Admin bar – dynamická pozícia: Zuno sa vloží ako predposledná položka

= 1.0.5 =
* Admin bar priority 200 – Zuno v skupine s ostatnými pluginmi, ku koncu

= 1.0.4 =
* Admin bar "Zuno" priority 9999 – zobrazí sa za WPCode, pred Skratky/Cache

= 1.0.3 =
* Admin bar "Zuno" presunutý na pravú stranu (top-secondary group)

= 1.0.2 =
* Predvolená veľkosť písma zmenená na 16px
* Admin bar "Zuno" presunutý ku koncu (pred skratky)

= 1.0.1 =
* Settings presunuté pod Settings menu (nie top-level)
* Admin bar "Zuno" dropdown bez ikonky, na štandardnej pozícii
* Odkaz "Nastavenia" v zozname pluginov smeruje správne

= 1.0.0 =
* Prvé vydanie pod značkou Zuno
* Vlastná kategória "Zuno" v blokovom editore (zelené ikonky)
* Top-level "Zuno" menu v admin sidebar
* Admin bar dropdown s odkazmi na nastavenia
* Hex color picker pre farbu akcentu
