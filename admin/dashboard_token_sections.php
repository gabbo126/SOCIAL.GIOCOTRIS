<?php
// Questo file contiene le sezioni token che verranno poi incluse nel file dashboard.php

// Funzione per generare le sezioni token
function generateTokenSection($token_type, $tokens_by_status, $color, $icon) {
    $section_id = strtolower($token_type);
    $has_tokens = !empty($tokens_by_status);
    ?>
    <!-- Token di <?php echo $token_type; ?> -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-<?php echo $icon; ?> me-2"></i>Token di <?php echo $token_type; ?></h5>
        </div>
        <div class="card-body">
            <?php if ($has_tokens): ?>
                <!-- Tab per stati token -->
                <ul class="nav nav-tabs mb-3" id="<?php echo $section_id; ?>TokensTabs" role="tablist">
                    <?php 
                    $active_tab = true;
                    foreach($tokens_by_status as $status => $tokens): 
                        if (!empty($tokens)):
                            $tab_id = "{$section_id}-{$status}";
                            $active_class = $active_tab ? 'active' : '';
                    ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $active_class; ?>" 
                                id="<?php echo $tab_id; ?>-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#<?php echo $tab_id; ?>" 
                                type="button" 
                                role="tab">
                            <?php echo ucfirst($status); ?> 
                            <span class="badge rounded-pill bg-secondary ms-1"><?php echo count($tokens); ?></span>
                        </button>
                    </li>
                    <?php 
                            $active_tab = false;
                        endif;
                    endforeach; 
                    ?>
                </ul>
                
                <!-- Contenuto tab per token -->
                <div class="tab-content" id="<?php echo $section_id; ?>TokensContent">
                    <?php 
                    $active_tab = true;
                    foreach($tokens_by_status as $status => $tokens): 
                        if (!empty($tokens)):
                            $pane_id = "{$section_id}-{$status}";
                            $pane_class = $active_tab ? 'show active' : '';
                            $total_tokens = count($tokens);
                            $initial_display = 8; // Numero di token da mostrare inizialmente
                            $has_more = $total_tokens > $initial_display;
                    ?>
                    <div class="tab-pane fade <?php echo $pane_class; ?>" id="<?php echo $pane_id; ?>" role="tabpanel">
                        <!-- Container per i token visibili inizialmente -->
                        <div class="row tokens-container" id="tokens-container-<?php echo $pane_id; ?>">
                            <?php foreach(array_slice($tokens, 0, $initial_display) as $token): 
                                switch ($token['status']) {
                                    case 'attivo': $status_class = 'bg-success'; break;
                                    case 'usato': $status_class = 'bg-secondary'; break;
                                    case 'disattivato': $status_class = 'bg-warning text-dark'; break;
                                    case 'scaduto': $status_class = 'bg-danger'; break;
                                    default: $status_class = 'bg-secondary';
                                }
                            ?>
                            <div class="col-md-4 col-lg-3 mb-2">
                                <div class="card shadow-sm border-start border-2 border-<?php echo $color; ?> h-100">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <small><i class="bi bi-<?php echo $icon; ?>-fill me-1 text-<?php echo $color; ?>"></i></small>
                                        <span class="badge <?php echo $status_class; ?> badge-sm"><?php echo ucfirst($token['status']); ?></span>
                                    </div>
                                    <div class="card-body py-2 px-3">
                                        <p class="card-text mb-1">
                                            <?php if(isset($token['nome_azienda']) && $token['nome_azienda']): ?>
                                                <small><?php echo htmlspecialchars(substr($token['nome_azienda'], 0, 15)); ?><?php echo (strlen($token['nome_azienda']) > 15) ? '...' : ''; ?></small><br>
                                            <?php endif; ?>
                                            <small class="text-muted"><code><?php echo substr($token['token'], 0, 12); ?>...</code></small>
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted"><i class="bi bi-calendar-x"></i> <?php echo date('d/m/y', strtotime($token['data_scadenza'])); ?></small>
                                        </div>
                                    </div>
                                    <div class="card-footer py-2 px-3 d-flex justify-content-between align-items-center">
                                        <?php if ($token['status'] === 'attivo'): ?>
                                        <form action="token_manager.php" method="POST" class="d-inline" onsubmit="return confirm('Sei sicuro di voler disattivare questo token?');">
                                            <input type="hidden" name="action" value="deactivate_token">
                                            <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-sm py-0 px-1" title="Disattiva"><i class="bi bi-x-circle"></i></button>
                                        </form>
                                        <?php else: ?>
                                            <small>&nbsp;</small>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#tokenModal-<?php echo $token_type; ?>-<?php echo $token['id']; ?>">
                                            <small>Dettagli</small>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Modal per dettagli token -->
                                <div class="modal fade" id="tokenModal-<?php echo $token_type; ?>-<?php echo $token['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Dettagli Token</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Tipo:</strong> <?php echo $token_type; ?></p>
                                                <?php if(isset($token['nome_azienda']) && $token['nome_azienda']): ?>
                                                    <p><strong>Azienda:</strong> <?php echo htmlspecialchars($token['nome_azienda']); ?></p>
                                                <?php endif; ?>
                                                <p><strong>Token:</strong> <code><?php echo $token['token']; ?></code></p>
                                                <p><strong>Stato:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($token['status']); ?></span></p>
                                                <p><strong>Creato il:</strong> <?php echo date('d/m/Y H:i', strtotime($token['data_creazione'])); ?></p>
                                                <p><strong>Scade il:</strong> <?php echo date('d/m/Y H:i', strtotime($token['data_scadenza'])); ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <?php if ($token['status'] === 'attivo'): ?>
                                                <form action="token_manager.php" method="POST" onsubmit="return confirm('Sei sicuro di voler disattivare questo token?');">
                                                    <input type="hidden" name="action" value="deactivate_token">
                                                    <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Disattiva Token</button>
                                                </form>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($has_more): ?>
                        <!-- Pulsante "Vedi altri" -->
                        <div class="row mt-2 mb-3" id="show-more-container-<?php echo $pane_id; ?>">
                            <div class="col-12 text-center">
                                <button type="button" class="btn btn-outline-secondary show-more-btn" data-target="<?php echo $pane_id; ?>">
                                    Vedi altri token (<?php echo $total_tokens - $initial_display; ?>)
                                    <i class="bi bi-chevron-down ms-1"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Container per i token aggiuntivi (nascosto inizialmente) -->
                        <div class="additional-tokens-container" id="additional-tokens-container-<?php echo $pane_id; ?>" style="display: none;">
                            <!-- Barra superiore con pulsante "Meno" -->
                            <div class="row mb-3 sticky-top less-container" data-target="<?php echo $pane_id; ?>">
                                <div class="col-12 d-flex justify-content-between align-items-center bg-light py-2 px-3 rounded shadow-sm">
                                    <span class="text-muted">Token aggiuntivi (<?php echo $total_tokens - $initial_display; ?>)</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary show-less-btn" data-target="<?php echo $pane_id; ?>">
                                        <i class="bi bi-dash-circle me-1"></i> Chiudi
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Token aggiuntivi -->
                            <div class="row" id="additional-tokens-<?php echo $pane_id; ?>">
                                <?php foreach(array_slice($tokens, $initial_display) as $token): 
                                    switch ($token['status']) {
                                        case 'attivo': $status_class = 'bg-success'; break;
                                        case 'usato': $status_class = 'bg-secondary'; break;
                                        case 'disattivato': $status_class = 'bg-warning text-dark'; break;
                                        case 'scaduto': $status_class = 'bg-danger'; break;
                                        default: $status_class = 'bg-secondary';
                                    }
                                ?>
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <div class="card shadow-sm border-start border-2 border-<?php echo $color; ?> h-100">
                                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                            <small><i class="bi bi-<?php echo $icon; ?>-fill me-1 text-<?php echo $color; ?>"></i></small>
                                            <span class="badge <?php echo $status_class; ?> badge-sm"><?php echo ucfirst($token['status']); ?></span>
                                        </div>
                                        <div class="card-body py-2 px-3">
                                            <p class="card-text mb-1">
                                                <?php if(isset($token['nome_azienda']) && $token['nome_azienda']): ?>
                                                    <small><?php echo htmlspecialchars(substr($token['nome_azienda'], 0, 15)); ?><?php echo (strlen($token['nome_azienda']) > 15) ? '...' : ''; ?></small><br>
                                                <?php endif; ?>
                                                <small class="text-muted"><code><?php echo substr($token['token'], 0, 12); ?>...</code></small>
                                            </p>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted"><i class="bi bi-calendar-x"></i> <?php echo date('d/m/y', strtotime($token['data_scadenza'])); ?></small>
                                            </div>
                                        </div>
                                        <div class="card-footer py-2 px-3 d-flex justify-content-between align-items-center">
                                            <?php if ($token['status'] === 'attivo'): ?>
                                            <form action="token_manager.php" method="POST" class="d-inline" onsubmit="return confirm('Sei sicuro di voler disattivare questo token?');">
                                                <input type="hidden" name="action" value="deactivate_token">
                                                <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-sm py-0 px-1" title="Disattiva"><i class="bi bi-x-circle"></i></button>
                                            </form>
                                            <?php else: ?>
                                                <small>&nbsp;</small>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#tokenModal-<?php echo $token_type; ?>-add-<?php echo $token['id']; ?>">
                                                <small>Dettagli</small>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal per dettagli token aggiuntivi -->
                                    <div class="modal fade" id="tokenModal-<?php echo $token_type; ?>-add-<?php echo $token['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Dettagli Token</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Tipo:</strong> <?php echo $token_type; ?></p>
                                                    <?php if(isset($token['nome_azienda']) && $token['nome_azienda']): ?>
                                                        <p><strong>Azienda:</strong> <?php echo htmlspecialchars($token['nome_azienda']); ?></p>
                                                    <?php endif; ?>
                                                    <p><strong>Token:</strong> <code><?php echo $token['token']; ?></code></p>
                                                    <p><strong>Stato:</strong> <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($token['status']); ?></span></p>
                                                    <p><strong>Creato il:</strong> <?php echo date('d/m/Y H:i', strtotime($token['data_creazione'])); ?></p>
                                                    <p><strong>Scade il:</strong> <?php echo date('d/m/Y H:i', strtotime($token['data_scadenza'])); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <?php if ($token['status'] === 'attivo'): ?>
                                                    <form action="token_manager.php" method="POST" onsubmit="return confirm('Sei sicuro di voler disattivare questo token?');">
                                                        <input type="hidden" name="action" value="deactivate_token">
                                                        <input type="hidden" name="token_id" value="<?php echo $token['id']; ?>">
                                                        <button type="submit" class="btn btn-danger">Disattiva Token</button>
                                                    </form>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php 
                            $active_tab = false;
                        endif;
                    endforeach; 
                    ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> Nessun token di <?php echo strtolower($token_type); ?> presente al momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>
