<!-- resources/views/livewire/cadastro-diarias.blade.php -->
<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-dark">
        <div class="card-header">
            <h3 class="card-title">Cadastro de Diárias Asa Branca</h3>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="salvar">
                <!-- Form content remains the same -->
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="nome">Nome do Funcionário:</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="nome" wire:model.live.debounce.300ms="nome" placeholder="Digite o nome do funcionário">
                            @if(count($sugestoesFuncionarios) > 0)
                                <div class="position-absolute bg-white w-100 mt-1 shadow-sm rounded z-index-dropdown">
                                    <ul class="list-group">
                                        @foreach($sugestoesFuncionarios as $funcionario)
                                            <li class="list-group-item list-group-item-action cursor-pointer" wire:click="selecionarFuncionario({{ $funcionario->id }})">
                                                {{ $funcionario->nome }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        @error('nome') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="departamento">Departamento:</label>
                        <input type="text" class="form-control" id="departamento" wire:model="departamento" readonly>
                        @error('departamento') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="funcao">Função:</label>
                        <input type="text" class="form-control" id="funcao" wire:model="funcao" readonly>
                        @error('funcao') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1 form-group">
                        <label for="valor">Valor:</label>
                        <input type="text" class="form-control" id="valor" wire:model="valor" readonly>
                        @error('valor') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1 form-group">
                        <label for="horasExtras">Horas Extras:</label>
                        <input type="number" class="form-control" id="horasExtras" wire:model.live="horasExtras">
                        @error('horasExtras') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-1 form-group">
                        <label for="qtdDiaria">Qtd. de Diária:</label>
                        <input type="text" class="form-control" id="qtdDiaria" wire:model="qtdDiaria" readonly>
                        @error('qtdDiaria') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-2 form-group">
                        <label for="referencia">Referência:</label>
                        <select class="form-control" id="referencia" wire:model="referencia">
                            <option value="">Selecione</option>
                            @foreach($referencias as $ref)
                                <option value="{{ $ref }}">{{ $ref }}</option>
                            @endforeach
                        </select>
                        @error('referencia') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 form-group">
                        <label for="observacao">Observação:</label>
                        <textarea class="form-control" id="observacao" wire:model="observacao" rows="3" placeholder="Digite alguma observação (opcional)"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-secondary">Salvar</button>
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#modalVisualizarDiarias">Visualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para visualizar diárias -->
    <div class="modal fade" id="modalVisualizarDiarias" tabindex="-1" role="dialog" aria-labelledby="modalVisualizarDiariasLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVisualizarDiariasLabel">Diárias Temporárias</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(count($diariasSalvas) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Departamento</th>
                                        <th>Função</th>
                                        <th>Valor</th>
                                        <th>Horas Extras</th>
                                        <th>Qtd. Diária</th>
                                        <th>Referência</th>
                                        <th>Observação</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($diariasSalvas as $diaria)
                                        <tr>
                                            <td>{{ $diaria['nome'] }}</td>
                                            <td>{{ $diaria['departamento'] }}</td>
                                            <td>{{ $diaria['funcao'] }}</td>
                                            <td>{{ $diaria['valor'] }}</td>
                                            <td>{{ $diaria['horasExtras'] }}</td>
                                            <td>{{ $diaria['qtdDiaria'] }}</td>
                                            <td>{{ $diaria['referencia'] }}</td>
                                            <td>{{ $diaria['observacao'] }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger" wire:click="removerDiaria('{{ $diaria['id'] }}')">
                                                    <i class="fas fa-trash"></i> Apagar
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Total:</strong></td>
                                        <td>
                                            <strong>
                                                {{ array_sum(array_column($diariasSalvas, 'qtdDiaria')) }}
                                            </strong>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-success" wire:click="salvarDiarias">
                                <i class="fas fa-save"></i> Salvar Diárias
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Nenhuma diária adicionada temporariamente.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .z-index-dropdown {
            z-index: 1050;
        }
        .cursor-pointer {
            cursor: pointer;
        }
    </style>

    @push('js')
    <script>
        // Script para garantir que o modal seja resetado corretamente
        window.addEventListener('closeModal', event => {
            $('#modalVisualizarDiarias').modal('hide');
        });
    </script>
    @endpush
</div>