<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function getTableName(): string
    {
        return config('translation-overrides.table_name', 'translation_overrides');
    }
    
    protected function getTenantIdColumn(): string
    {
        return config('translation-overrides.tenant_id_column', 'tenant_id');
    }
    
    protected function isTenancyEnabled(): bool
    {
        return config('translation-overrides.tenancy_enabled', true);
    }
    
    public function up(): void
    {
        Schema::create($this->getTableName(), function (Blueprint $table) {
            $table->id();
            
            // Only add tenant_id column if tenancy is enabled
            if ($this->isTenancyEnabled()) {
                $table->integer($this->getTenantIdColumn())->index();
            }
            
            $table->string('locale');
            $table->string('key');
            $table->text('value');
            
            // Create unique constraint based on tenancy mode
            if ($this->isTenancyEnabled()) {
                $table->unique([$this->getTenantIdColumn(), 'locale', 'key']);
                $table->index([$this->getTenantIdColumn(), 'locale']);
            } else {
                $table->unique(['locale', 'key']);
                $table->index(['locale']);
            }
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }
};
