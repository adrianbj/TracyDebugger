<?php

use Tracy\IBarPanel;

/**
 * CSS panel
 */

class CssPanel implements IBarPanel {
    public function getTab() {
        return '
            <style>

                .tracy-panel a:link {
                    color: #125EAE !important;
                    background-color: transparent !important;
                }
                .tracy-panel a:focus {
                    color: #125EAE !important;
                }
                .tracy-panel a:hover {
                    color: #FFFFFF !important;
                    background-color: #125EAE !important;
                }

                .tracy-panel h3 {
                    display: block !important;
                    font-size: 1.17em !important;
                    font-weight: bold !important;
                    border: none !important;
                    padding-top: 1em !important;
                    color: #444444 !important;
                }

                #tracy-debug h1 {
                    max-width: calc(100vw - 40px) !important;
                    padding-right: 35px !important;
                }

                #tracy-debug input[type="checkbox"],
                #tracy-debug input[type="radio"] {
                    width: 12px !important;
                    height: 12px !important;
                    position: static !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }

                #tracy-debug input[type="text"] {
                    font-family: sans-serif;
                    height: inherit !important;
                    width: initial !important;
                    display: inline-block !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    border-radius: 0 !important;
                }

                #tracy-debug, #tracy-debug h2, #tracy-debug legend, #tracy-debug p {
                    font-family: sans-serif !important;
                    font-weight: normal !important;
                    color: #333333 !important;
                }

                #tracy-debug, #tracy-debug legend, #tracy-debug p, #tracy-debug select {
                    font-size: 12px !important;
                }

                #tracy-debug select {
                    height: 26px !important;
                    width: initial !important;
                    border-radius: 0 !important;
                    margin: 0 !important;
                    display: inline-block !important;
                }

                #tracy-debug label {
                    margin:0 !important;
                }

                #tracy-debug a {
                    border: none !important;
                }

                #tracy-debug table {
                    width: initial !important;
                    margin: 0 !important;
                    box-shadow: none !important;
                }

                #tracy-debug .tracy-panel td,
                #tracy-debug .tracy-panel th {
                    font-family: sans-serif !important;
                    font-weight: normal !important;
                    font-size: 12px !important;
                    color: #444444 !important;
                    padding: 3px !important;
                }

                #tracy-debug input[type="submit"] {
                    width: initial !important;
                    height: initial !important;
                    display: inline-block !important;
                    background: #FFFFFF !important;
                    margin: 0 !important;
                    padding: 3px !important;
                    border: 1px solid #D2D2D2 !important;
                    -webkit-border-radius: 5px !important;
                    -moz-border-radius: 5px !important;
                    border-radius: 5px !important;
                    cursor: pointer !important;
                    text-transform: none !important;
                    font: normal normal 12px/1.5 sans-serif;
                    color: #444444;
                }

                #tracy-debug fieldset {
                    all: initial !important;
                    font: inherit !important;
                    color: inherit !important;
                }

                .tracy-panel h2 {
                    font-size: 20px !important;
                }

                .tracy-inner {
                    max-height: calc(100vh - 85px) !important;
                    max-width: calc(100vw - 40px) !important;
                    padding-right: 30px !important;
                }

                .tracy-inner fieldset label {
                    display: block !important;
                }

                .tracy-inner p {
                    font-size:15px !important;
                }

                #tracy-debug pre.tracy-dump {
                    padding: .4em 2em .4em .7em !important;
                }

                .tracy-inner td {
                    word-wrap: break-word !important;
                }

                .tracy-inner td.tracy-force-break {
                    /*overflow-wrap: break-word;*/
                    /*-ms-word-break: break-all;*/
                    /* This is the dangerous one in WebKit, as it breaks things wherever */
                    /*word-break: break-all;*/
                    /* Instead use this non-standard one: */
                    /*word-break: break-word;*/
                    white-space: pre;           /* CSS 2.0 */
                    white-space: pre-wrap;      /* CSS 2.1 */
                    white-space: pre-line;      /* CSS 3.0 */
                    white-space: -pre-wrap;     /* Opera 4-6 */
                    white-space: -o-pre-wrap;   /* Opera 7 */
                    white-space: -moz-pre-wrap; /* Mozilla */
                    white-space: -hp-pre-wrap;  /* HP Printers */
                    word-wrap: break-word;      /* IE 5+ */
                }

                .tracy-toggle {
                    font-size: 15px !important;
                }

                .pw-admin-links a {
                    transition: transform 0.12s;
                }
                .pw-admin-links a:hover svg {
                    transform: scale(1.4);
                }
                .pw-admin-links a:hover, .pw-admin-links a:active, .pw-admin-links a:focus {
                    background-color: transparent !important;
                }

            }
            </style>
        ';
    }



    public function getPanel() {
        return false;
    }

}
