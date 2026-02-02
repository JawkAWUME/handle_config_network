<?php
// app/Services/Contracts/ExportServiceInterface.php

namespace App\Services\Contracts;

interface ExportServiceInterface
{
    /**
     * Exporter des données au format Excel
     */
    public function exportToExcel(string $type, array $options = []): string;

    /**
     * Exporter des données au format PDF
     */
    public function exportToPDF(string $type, array $options = []): string;

    /**
     * Exporter des configurations au format texte
     */
    public function exportConfigurationsToText(array $options = []): string;

    /**
     * Générer un rapport d'audit
     */
    public function generateAuditReport(array $options = []): string;

    /**
     * Générer un rapport de statistiques
     */
    public function generateStatisticsReport(array $options = []): string;

    /**
     * Générer un rapport d'inventaire
     */
    public function generateInventoryReport(array $options = []): string;
}