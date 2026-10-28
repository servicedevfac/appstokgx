<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'categorie',
        'montant',
        'date_depense',
        'mode_paiement',
        'beneficiaire',
        'justificatif',
        'description',
        'user_id',
    ];

    protected $casts = [
        'date_depense' => 'date',
        'montant' => 'decimal:2',
    ];

    /**
     * Utilisateur ayant enregistré la dépense
     */
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catégories de dépenses disponibles avec descriptions
     */
    public static function categories(): array
    {
        return [
            'Loyer' => [
                'label' => 'Loyer & Local',
                'badge' => 'primary',
                'color' => '#1a237e',
                'icon' => 'fas fa-building',
            ],
            'Électricité & Eau' => [
                'label' => 'Électricité & Eau (CIE / SODECI)',
                'badge' => 'warning',
                'color' => '#f59e0b',
                'icon' => 'fas fa-bolt',
            ],
            'Transport & Logistique' => [
                'label' => 'Transport & Livraison',
                'badge' => 'info',
                'color' => '#06b6d4',
                'icon' => 'fas fa-truck',
            ],
            'Salaires & Rémunérations' => [
                'label' => 'Salaires & Rémunérations',
                'badge' => 'purple',
                'color' => '#8b5cf6',
                'icon' => 'fas fa-users-cog',
            ],
            'Fournitures & Emballages' => [
                'label' => 'Fournitures & Emballages',
                'badge' => 'success',
                'color' => '#10b981',
                'icon' => 'fas fa-box-open',
            ],
            'Marketing & Communication' => [
                'label' => 'Marketing & Communication',
                'badge' => 'danger',
                'color' => '#ef4444',
                'icon' => 'fas fa-bullhorn',
            ],
            'Entretien & Réparations' => [
                'label' => 'Entretien & Réparations',
                'badge' => 'secondary',
                'color' => '#64748b',
                'icon' => 'fas fa-tools',
            ],
            'Taxes & Impôts' => [
                'label' => 'Taxes & Impôts municipaux',
                'badge' => 'dark',
                'color' => '#334155',
                'icon' => 'fas fa-receipt',
            ],
            'Autre dépense' => [
                'label' => 'Autre dépense diverse',
                'badge' => 'secondary',
                'color' => '#94a3b8',
                'icon' => 'fas fa-wallet',
            ],
        ];
    }

    /**
     * Modes de paiement disponibles
     */
    public static function modesPaiement(): array
    {
        return [
            'Espèces' => 'Espèces (Cash)',
            'Wave' => 'Wave',
            'Orange Money' => 'Orange Money',
            'MTN MoMo' => 'MTN Mobile Money',
            'Moov Money' => 'Moov Money',
            'Virement bancaire' => 'Virement bancaire',
            'Chèque' => 'Chèque',
            'Autre' => 'Autre moyen',
        ];
    }

    /**
     * Scopes utiles
     */
    public function scopeCeMois($query)
    {
        return $query->whereYear('date_depense', now()->year)
                     ->whereMonth('date_depense', now()->month);
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_depense', today());
    }

    public function scopeCategorie($query, $cat)
    {
        if ($cat) {
            return $query->where('categorie', $cat);
        }
        return $query;
    }

    /**
     * Obtenir la liste simple cle => libelle des categories
     */
    public static function categoriesList(): array
    {
        $list = [];
        foreach (self::categories() as $key => $info) {
            $list[$key] = $info['label'];
        }
        return $list;
    }

    /**
     * Accesseur pour le libellé de la catégorie
     */
    public function getCategorieLabelAttribute(): string
    {
        $cats = self::categories();
        return $cats[$this->categorie]['label'] ?? ($this->categorie ?? 'Non catégorisé');
    }
}
