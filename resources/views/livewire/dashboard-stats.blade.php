<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $tarefasPendentes }}</h3>
                <p>Tarefas Pendentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('rh.tarefas') }}" class="small-box-footer">
                Ver tarefas <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $tarefasEmAndamento }}</h3>
                <p>Tarefas em Andamento</p>
            </div>
            <div class="icon">
                <i class="fas fa-tasks"></i>
            </div>
            <a href="{{ route('rh.tarefas-por-usuarios') }}" class="small-box-footer">
                Ver detalhes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $tarefasConcluidas }}</h3>
                <p>Tarefas Concluídas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('rh.administrador') }}" class="small-box-footer">
                Ver todas <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $taxaConclusao }}%</h3>
                <p>Taxa de Conclusão</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <a href="{{ route('rh.administrador') }}" class="small-box-footer">
                Mais informações <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div> 