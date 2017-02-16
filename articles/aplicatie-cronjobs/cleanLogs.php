<?php
/**
 * Copyright (C) 2017 Mihai Surdeanu. All rights reserved.
 */


/*
 * Folosind variabila de mai jos, se poate specifica calea directoarelor
 * in care este permisa cautarea... Cautarea nu este una recursiva!
 */
$DIRECTORIES = array("/home/{CPANEL_USERNAME}/tmp/awstats/");

/*
 * Doar fisierele cu extensia aflata in vectorul de mai jos pot fi sterse.
 */
 $EXTENSIONS = array("txt");
 
/*
 * Prin intermediul acestui script se vor sterge doar fisierele create
 * acum AFTER_X_DAYS zile.
 */
 $AFTER_X_DAYS = 7;
 
/*
 * Prin completarea campului de mai jos cu o adresa de email valida, veti
 * activa procesul de notificare.
 */
 $EMAIL = '';
 
/* =============================
 * NU MODIFICA NIMIC DE MAI JOS!
 */
date_default_timezone_set("Europe/Bucharest");
$ofile = strtotime("-{$AFTER_X_DAYS} day");
$total_space = $total_files = 0;
// Iteram asupra fiecarui director specificat de utilizator.
foreach ($DIRECTORIES as $directory) {
	// Exista directorul specificat?
	if ($handle = opendir($directory)) {\
		// Obtinem toate fisierele / directoarele cuprinse in directorul curent.
		// Nu se vor trata subdirectoarele gasite!
		while (($entry = readdir($handle)) !== false) {
			if ($entry == "." || $entry == ".." ||
				!is_file($directory . $entry)) {
					continue;
			}
			// Cand a fost creat fisierul?
			$cfile = filectime($directory . $entry);
			// Este mai vechi decat data specificata de administrator?
			if ($cfile < $ofile) {
				// Are extensia dorita?
				foreach ($EXTENSIONS as $extension) {
					// Verificam daca exensia exista un numele fisierului...
					if (strpos($entry, "." . $extension) !== false) {
						$total_space += filesize($directory . "/" . $entry);
						$total_files++;
						// Atunci putem sterge fisierul!
						unlink($directory . $entry);
						
						// Nu mai are rost sa cautam o alta potrivire...
						break;
					}
				}
				// Pentru o performanta sporita, este bine ca extensiile cele
				// mai uzuale sa fie puse cat mai la inceput, in cadrul
				// vectorului de configurare
			}
		}	
		// Nu uitam sa inchidem directorul deschis.
		closedir($handle);
	}
}

// Vom trimite si un email de notificare?
if (filter_var($EMAIL, FILTER_VALIDATE_EMAIL)) {
	// Memoria ocupata va fi afisata in MB...
	$total_space = number_format($total_space / (1024.0 * 1024.0), 2);
	$message = "Salut\nIn urma rularii scriptului, un numar de aproximativ ";
	$message .= "{$total_files} au fost sterse de pe disc, totalizand {$total_space} MB.";
	$subject = "Notificare cleanLogs";
	mail(EMAIL, $subject, $message);
}

echo "Scriptul a fost rulat cu succes.";
