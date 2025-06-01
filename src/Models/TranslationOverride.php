<?php

namespace JaysonTemporas\TranslationOverrides\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationOverride extends Model
{
    protected $fillable = [
        'locale',
        'key',
        'value',
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('translation-overrides.table_name', 'translation_overrides'));
        
        // Add tenant_id to fillable only if tenancy is enabled
        if ($this->isTenancyEnabled()) {
            $this->fillable[] = $this->getTenantIdColumn();
        }
    }

    public function team(): BelongsTo
    {
        if (!$this->isTenancyEnabled()) {
            throw new \Exception('Team relationship is not available when tenancy is disabled.');
        }
        
        $tenantIdColumn = $this->getTenantIdColumn();
        return $this->belongsTo(config('translation-overrides.tenant_model'), $tenantIdColumn);
    }

    public function clearTenantCache(): void
    {
        $tableName = $this->getTable();
        
        if ($this->isTenancyEnabled()) {
            $tenantIdColumn = $this->getTenantIdColumn();
            cache()->forget("{$tableName}:{$this->$tenantIdColumn}");
        } else {
            cache()->forget("{$tableName}:global");
        }
    }
    
    protected function isTenancyEnabled(): bool
    {
        return config('translation-overrides.tenancy_enabled', true);
    }
    
    protected function getTenantIdColumn(): string
    {
        return config('translation-overrides.tenant_id_column', 'tenant_id');
    }
}
