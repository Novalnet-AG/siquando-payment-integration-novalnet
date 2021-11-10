// Novalnet language file for German

define('CC_PAYMENT_NOVALNETGATEWAY', '<cc:print value="&mypaymentscript.novalnetpayments.filename">'); 
define('CC_SHOP_NNLANG', 'DE');
define('CC_SITE_NNBASICPARAM', 'Ungültige Parameter für die Händlereinstellungen');
define('CC_SITE_NNTESTORDER', 'Testbestellung');
define('CC_SITE_NNTESTORDER_MESSAGE', 'Die Zahlung wird im Testmodus durchgeführt, daher wird der Betrag für diese Transaktion nicht eingezogen.');
define('CC_SITE_SEPA_DUE_DATE_ERROR', 'SEPA Fälligkeitsdatum Ungültiger');
define('CC_SITE_DUE_DATE_ERROR', 'Ungültiges Fälligkeitsdatum');
define('CC_SITE_NNTRANSINFO', 'Überweisen Sie bitte den Betrag an die unten aufgeführte Bankverbindung unseres Zahlungsdienstleisters Novalnet.');
define('CC_SITE_NNTRANSVALIDUNTIL', 'Fälligkeitsdatum:');
define('CC_SITE_NNTRANSACCHOLDER', 'Kontoinhaber:');
define('CC_SITE_NNBANKNAME', 'Bank:');
define('CC_SITE_NNBANKIBAN', 'IBAN:');
define('CC_SITE_NNBANKBIC', 'BIC:');
define('CC_SITE_NNSEPAACCOUNTHOLDER', 'Kontoinhaber');
define('CC_SITE_NNSEPAIBAN', 'IBAN');
define('CC_SITE_NNSEPAMANDATECONFIRM', '<b>Ich erteile hiermit das SEPA-Lastschriftmandat (elektronische Übermittlung) und bestätige, dass die Bankverbindung korrekt ist.</b>');
define('CC_SITE_NNACCOUNTERRORMSG', 'Ihre Kontodaten sind ungültig');
define('CC_SITE_NNCARDERRORMSG', 'Ihre Kreditkartendaten sind ungültig');
define('CC_SITE_NNINVOICEREFDESCMORE', 'Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke für die überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('CC_SITE_NNINVOICEREFTEXTSINGLE', 'Verwendungszweck ');
define('CC_SITE_NNMULTIBANCOREFDESC', 'Bitte verwenden Sie diese Zahlungsreferenzdaten, um am Multibanco-Geldautomaten oder per Onlinebanking zu bezahlen.
Wählen Sie in Ihrem Online-Konto oder am Geldautomaten "Zahlung und andere Dienstleistungen" und dann "Zahlungen von Dienstleistungen/Einkäufen".');
define('CC_SITE_NNMULTIBANCOREF', 'Referenz');
define('CC_SITE_NNMULTIBANCOENTITYNO', 'Entity-Nr.');
define('CC_SITE_NNAMOUNT', 'Betrag:');
define('CC_SITE_NNTRANSID', 'Novalnet Transaktions-ID:');
define('CC_SITE_NNCASHPAYMENTSLIPEXPIRYDATE', 'Verfallsdatum des Zahlscheins');
define('CC_SITE_NNCASHPAYMENTNEARSTORE', 'Barzahlen-Partnerfiliale in Ihrer Nähe');
define('CC_SITE_NNCASHPAYMENTNEARCHECKOUTBUTTONNAME', 'Bezahlen mit Barzahlen');
define('CC_SITE_CURL_NOT_INSTALLED','Sie müssen die CURL-Funktion auf Server aktivieren, überprüfen Sie bitte mit Ihrem Hosting-Provider darüber. ');
define('CC_SITE_NNREDIRECTTEXT', 'Nach der erfolgreichen Überprüfung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen.');
define('CC_SITE_NNDOB', 'Ihr Geburtsdatum');
define('CC_SITE_NNINVALIDDOB', 'Geben Sie ein gültiges Geburtsdatum ein');
define('CC_SITE_NNVALIDDOB', 'Sie müssen mindestens 18 Jahre alt sein.');
define('CC_SITE_NNGUARANTEE_TEXT','Diese Transaktion wird mit Zahlungsgarantie verarbeitet');
define('CC_SITE_NNGUARANTEE_INVOICE_PENDING_TEXT','Ihre Bestellung ist unter Bearbeitung. Sobald diese bestätigt wurde, erhalten Sie alle notwendigen Informationen zum Ausgleich der Rechnung. Wir bitten Sie zu beachten, dass dieser Vorgang bis zu 24 Stunden andauern kann.');
define('CC_SITE_NNGUARANTEE_INVALID_ADDRESS','Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Die Lieferadresse muss mit der Rechnungsadresse übereinstimmen)');
define('CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_COUNTRY',' Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Als Land ist nur Deutschland, Österreich oder Schweiz erlaubt)');
define('CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_AMOUNT',' Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Der Mindestbestellwert beträgt %s EUR)');
define('CC_SITE_NNGUARANTEE_GUARANTEE_INVALID_CURRENCY',' Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Als Währung ist nur EUR erlaubt)');
define('CC_SITE_NNTRANSACTION_CONFIRM_TEXT','Novalnet-Callback-Nachricht erhalten: Die Buchung wurde am %s Uhr bestätigt.');
define('CC_SITE_NNPENDING_TO_ONHOLD_TEXT','Novalnet-Callback-Nachricht erhalten: Der Status der Transaktion mit der TID: %s wurde am %s Uhr von ausstehend auf ausgesetzt geändert.');
define('CC_SITE_NNTRANSACTION_CANCELLATION_TEXT',' Novalnet-Callback-Nachricht erhalten: Die Transaktion wurde am %s Uhr storniert');
define('CC_SITE_NNLEVEL_ZERO_CALLBACK_PAID_TEXT',' Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag am %s Uhr.');
define('CC_SITE_NNCALLBACK_BOOKBACK_TEXT','Novalnet-Callback-Meldung erhalten: Rückerstattung / Bookback erfolgreich ausgeführt für die TID: %s: Betrag: %s am %s. TID der Folgebuchung: %s');
define('CC_SITE_NNCALLBACK_CHARGEBACK_TEXT',' Novalnet-Callback-Nachricht erhalten: Chargeback erfolgreich importiert für die TID: %s Betrag: %s am %s Uhr. TID der Folgebuchung: %s');
define('CC_SITE_NNCALLBACK_PAID_TEXT',' Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag %s am , um %s Uhr. Bitte suchen Sie nach der bezahlten Transaktion in unserer Novalnet-Händleradministration mit der TID: %s.');
define('CC_SITE_NNCALLBACK_ONLINE_TRANSFER_CREDIT_TEXT',' Der Betrag von %s für die Bestellung %s wurde bezahlt. Überprüfen Sie bitten den erhaltenen Betrag und die Details zur TID und aktualisieren Sie den Status der Bestellung entsprechend.');
define('CC_SITE_NNSEPA_INFO_TEXT1','Ich ermächtige den Zahlungsempfänger, Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von dem Zahlungsempfänger auf mein Konto gezogenen Lastschriften einzulösen.');
define('CC_SITE_NNSEPA_INFO_TEXT2','Gläubiger-Identifikationsnummer: DE53ZZZ00000004253');
define('CC_SITE_NNSEPA_INFO_TEXT3','Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.');
define('CC_SITE_NNTRANSACTION_CANCELLATION_TEXT','Novalnet-Callback-Nachricht erhalten: Die Transaktion wurde am %s Uhr storniert');

// Novalnet language file for German
