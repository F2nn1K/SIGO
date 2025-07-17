<div>
    <!-- Cards de estatísticas do RH -->
    <livewire:dashboard-stats />

    <!-- Gráficos e Cards Detalhados -->
    <div class="row">
        <!-- Tarefas por usuário -->
        <div class="col-md-6">
            <livewire:dashboard-tarefas />
        </div>
        
        <!-- Diárias por Departamento -->
        <div class="col-md-6">
            <livewire:dashboard-diarias />
        </div>
    </div>

    <div class="row">
        <!-- Últimas Tarefas Cadastradas -->
        <div class="col-md-6">
            <livewire:dashboard-ultimas-tarefas />
        </div>
        
        <!-- Últimas Diárias -->
        <div class="col-md-6">
            <livewire:dashboard-ultimas-diarias />
        </div>
    </div>
</div> 