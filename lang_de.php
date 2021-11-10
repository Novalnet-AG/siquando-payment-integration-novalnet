// Novalnet language file for German

define('CC_PAYMENT_NOVALNETGATEWAY', '<cc:print value="&mypaymentscript.novalnetpayments.filename">'); 
define('CC_SHOP_NNLANG', 'DE');
define('CC_SITE_NNBASICPARAM', 'Ung�ltige Parameter f�r die H�ndlereinstellungen');
define('CC_SITE_NNTESTORDER', 'Testbestellung');
define('CC_SITE_NNTESTORDER_MESSAGE', 'Die Zahlung wird im Testmodus durchgef�hrt, daher wird der Betrag f�r diese Transaktion nicht eingezogen.');
define('CC_SITE_SEPA_DUE_DATE_ERROR', 'SEPA F�lligkeitsdatum Ung�ltiger');
define('CC_SITE_DUE_DATE_ERROR', 'Ung�ltiges F�lligkeitsdatum');
define('CC_SITE_NNTRANSINFO', '�berweisen Sie bitte den Betrag an die unten aufgef�hrte Bankverbindung unseres Zahlungsdienstleisters Novalnet.');
define('CC_SITE_NNTRANSVALIDUNTIL', 'F�lligkeitsdatum:');
define('CC_SITE_NNTRANSACCHOLDER', 'Kontoinhaber:');
define('CC_SITE_NNBANKNAME', 'Bank:');
define('CC_SITE_NNBANKIBAN', 'IBAN:');
define('CC_SITE_NNBANKBIC', 'BIC:');
define('CC_SITE_NNSEPAACCOUNTHOLDER', 'Kontoinhaber');
define('CC_SITE_NNSEPAIBAN', 'IBAN');
define('CC_SITE_NNSEPAMANDATECONFIRM', '<b>Ich erteile hiermit das SEPA-Lastschriftmandat (elektronische �bermittlung) und best�tige, dass die Bankverbindung korrekt ist.</b>');
define('CC_SITE_NNACCOUNTERRORMSG', 'Ihre Kontodaten sind ung�ltig');
define('CC_SITE_NNCARDERRORMSG', 'Ihre Kreditkartendaten sind ung�ltig');
define('CC_SITE_NNINVOICEREFDESCMORE', 'Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke f�r die �berweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('CC_SITE_NNINVOICEREFTEXTSINGLE', 'Verwendungszweck ');
define('CC_SITE_NNMULTIBANCOREFDESC', 'Bitte verwenden Sie diese Zahlungsreferenzdaten, um am Multibanco-Geldautomaten oder per Onlinebanking zu bezahlen.
W�hlen Sie in Ihrem Online-Konto oder am Geldautomaten "Zahlung und andere Dienstleistungen" und dann "Zahlungen von Dienstleistungen/Eink�ufen".');
define('CC_SITE_NNMULTIBANCOREF', 'Referenz');
define('CC_SITE_NNMULTIBANCOENTITYNO', 'Entity-Nr.');
define('CC_SITE_NNAMOUNT', 'Betrag:');
define('CC_SITE_NNTRANSID', 'Novalnet Transaktions-ID:');
define('CC_SITE_NNCASHPAYMENTSLIPEXPIRYDATE', 'Verfallsdatum des Zahlscheins');
define('CC_SITE_NNCASHPAYMENTNEARSTORE', 'Barzahlen-Partnerfiliale in Ihrer N�he');
define('CC_SITE_NNCASHPAYMENTNEARCHECKOUTBUTTONNAME', 'Bezahlen mit Barzahlen');
define('CC_SITE_CURL_NOT_INSTALLED','Sie m�ssen die CURL-Funktion auf Server aktivieren, �berpr�fen Sie bitte mit Ihrem Hosting-Provider dar�ber. ');
define('CC_SITE_NNREDIRECTTEXT', 'Nach der erfolgreichen �berpr�fung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen.');
define('CC_SITE_NNDOB', 'Ihr Geburtsdatum');
define('CC_SITE_NNINVALIDDOB', 'Geben Sie ein g�ltiges Geburtsdatum ein');
define('CC_SITE_NNVALIDDOB', 'Sie m�ssen mindestens 18 Jahre alt sein.');
define('CC_SITE_NNGUARANTEE_TEXT','Diese Transaktion wird mit Zahlungsgarantie verarbeitet');
define('CC_SITE_NNGUARANTEE_INVOICE_PENDING_TEXT','Ihre Bestellung ist unter Bearbeitung. Sobald diese best�tigt wurde, erhalten Sie alle notwendigen Informationen zum Ausgleich der Rechnung. Wir bitten Sie zu beachten, dass dieser Vorgang bis zu 24 Stunden andauern kann.');
define('CC_SITE_NNGUARANTEE_INVALID_ADDRESS','Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen f�r die Zahlungsgarantie nicht erf�llt wurden (Die Lieferadresse muss mit der Rechnungsadresse �bereinstimmen)');
define('CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_COUNTRY',' Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen f�r die Zahlungsgarantie nicht erf�llt wurden (Als Land ist nur Deutschland, �sterreich oder Schweiz erlaubt)');
define('CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_AMOUNT',' Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen f�r die Zahlungsgarantie nicht erf�llt wurden (Der Mindestbestellwert betr�gt %s EUR)');
define('CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_CURRENCY',' Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen f�r die Zahlungsgarantie nicht erf�llt wurden (Als W�hrung ist nur EUR erlaubt)');
define('CC_SITE_NNTRANSACTION_CONFIRM_TEXT','Novalnet-Callback-Nachricht erhalten: Die Buchung wurde am %s Uhr best�tigt.');
define('CC_SITE_NNPENDING_TO_ONHOLD_TEXT','Novalnet-Callback-Nachricht erhalten: Der Status der Transaktion mit der TID: %s wurde am %s Uhr von ausstehend auf ausgesetzt ge�ndert.');
define('CC_SITE_NNTRANSACTION_CANCELLATION_TEXT',' Novalnet-Callback-Nachricht erhalten: Die Transaktion wurde am %s Uhr storniert');
define('CC_SITE_NNLEVEL_ZERO_CALLBACK_PAID_TEXT',' Novalnet-Callback-Skript erfolgreich ausgef�hrt f�r die TID: %s mit dem Betrag am %s Uhr.');
define('CC_SITE_NNCALLBACK_BOOKBACK_TEXT','Novalnet-Callback-Meldung erhalten: R�ckerstattung / Bookback erfolgreich ausgef�hrt f�r die TID: %s: Betrag: %s am %s. TID der Folgebuchung: %s');
define('CC_SITE_NNCALLBACK_CHARGEBACK_TEXT',' Novalnet-Callback-Nachricht erhalten: Chargeback erfolgreich importiert f�r die TID: %s Betrag: %s am %s Uhr. TID der Folgebuchung: %s');
define('CC_SITE_NNCALLBACK_PAID_TEXT',' Novalnet-Callback-Skript erfolgreich ausgef�hrt f�r die TID: %s mit dem Betrag %s am , um %s Uhr. Bitte suchen Sie nach der bezahlten Transaktion in unserer Novalnet-H�ndleradministration mit der TID: %s.');
define('CC_SITE_NNCALLBACK_ONLINE_TRANSFER_CREDIT_TEXT',' Der Betrag von %s f�r die Bestellung %s wurde bezahlt. �berpr�fen Sie bitten den erhaltenen Betrag und die Details zur TID und aktualisieren Sie den Status der Bestellung entsprechend.');
define('CC_SITE_NNSEPA_INFO_TEXT1','Ich erm�chtige den Zahlungsempf�nger, Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von dem Zahlungsempf�nger auf mein Konto gezogenen Lastschriften einzul�sen.');
define('CC_SITE_NNSEPA_INFO_TEXT2','Gl�ubiger-Identifikationsnummer: DE53ZZZ00000004253');
define('CC_SITE_NNSEPA_INFO_TEXT3','Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.');
define('CC_SITE_NNTRANSACTION_CANCELLATION_TEXT','Novalnet-Callback-Nachricht erhalten: Die Transaktion wurde am %s Uhr storniert');

// Novalnet language file for German
