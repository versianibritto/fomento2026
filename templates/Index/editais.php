<style>
    .anexo-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #d7e3f4;
        background: #f7faff;
        color: #24405e;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        transition: all .15s ease-in-out;
    }

    .anexo-chip:hover {
        background: #eaf3ff;
        border-color: #b8d2f2;
        color: #15324f;
    }

    .anexo-chip-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #dfeeff;
        color: #255c91;
    }

    .anexo-chip-icon svg {
        width: 10px;
        height: 10px;
    }
    
    .edital-corrente {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        align-items: start;
    }

    @media (max-width: 991.98px) {
        .edital-corrente {
            grid-template-columns: 1fr;
        }
    }

    .edital-corrente .editalItem {
        margin: 0;
        float: none;
        width: 100%;
        height: auto;
        min-height: 170px;
        display: block;
        padding: 0 10px;
        box-sizing: border-box;
    }

    .edital-corrente .editalItem .border {
        height: 100%;
    }

    .vitrine-open-badge-solid {
        display: inline-block;
        background: #2fa866;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .35px;
        padding: 6px 10px;
        border-radius: 999px;
        margin-bottom: 8px;
        line-height: 1.1;
    }
</style>
<section>
    <div class="main ajuste">
        <h1 class="tituloEM">Editais</h1>
        <div class="barraTituloEM"></div>
        <?php
        $anoAtual = (int)date('Y');
        $vitrinesAnoAtual = [];
        $vitrinesPorAno = [];

        foreach ($vitrines as $vitrine) {
            $anoDivulgacao = $vitrine->divulgacao ? (int)$vitrine->divulgacao->format('Y') : 0;
            if ($anoDivulgacao === $anoAtual) {
                $vitrinesAnoAtual[] = $vitrine;
                continue;
            }
            $chaveAno = $anoDivulgacao > 0 ? (string)$anoDivulgacao : 'Sem ano';
            if (!isset($vitrinesPorAno[$chaveAno])) {
                $vitrinesPorAno[$chaveAno] = [];
            }
            $vitrinesPorAno[$chaveAno][] = $vitrine;
        }

        if (isset($vitrinesPorAno['Sem ano'])) {
            $semAno = $vitrinesPorAno['Sem ano'];
            unset($vitrinesPorAno['Sem ano']);
        } else {
            $semAno = [];
        }
        krsort($vitrinesPorAno, SORT_NUMERIC);
        if (!empty($semAno)) {
            $vitrinesPorAno['Sem ano'] = $semAno;
        }
        $agoraTs = time();
        $vitrineAbertaAgora = static function ($vitrine, int $agoraTs): bool {
            if (empty($vitrine->inicio) || empty($vitrine->fim)) {
                return false;
            }
            try {
                return $vitrine->inicio->getTimestamp() <= $agoraTs
                    && $vitrine->fim->getTimestamp() >= $agoraTs;
            } catch (\Throwable $e) {
                return false;
            }
        };
        ?>

        <?php if (empty($vitrinesAnoAtual) && empty($vitrinesPorAno)) { ?>
            <div class="alert alert-info text-center">Nenhuma vitrine disponível no momento.</div>
        <?php } ?>

        <details open>
            <summary><strong><?= $anoAtual ?></strong> (correntes)</summary>
            <div class="edital mt-3 edital-corrente">
                <?php if (empty($vitrinesAnoAtual)) { ?>
                    <div>Não há vitrines correntes.</div>
                <?php } ?>
                <?php foreach ($vitrinesAnoAtual as $vitrine) { ?>
                    <div class="editalItem">
                        <div class="border">
                            <div class="editais box-simple">
                                <?php if ($vitrineAbertaAgora($vitrine, $agoraTs)) { ?>
                                    <span class="vitrine-open-badge-solid" title="Edital aberto">Edital aberto</span>
                                <?php } ?>
                                <div class="box-simple-title h4"><?= $vitrine->nome ?></div>
                                <?php if (!empty($vitrine->obs)) { ?>
                                    <div class="box-simple-time"><?= $vitrine->obs ?></div>
                                <?php } ?>
                            </div>
                            <div class="p-2">
                                <div class="mb-1">
                                    <?php if (!empty($vitrine->anexo_edital)) { ?>
                                        <button type="button" class="anexo-chip" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_edital) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Edital</button>
                                    <?php } ?>
                                    <?php if (!empty($vitrine->anexo_modelo_consentimento)) { ?>
                                        <button type="button" class="anexo-chip me-2" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_modelo_consentimento) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Modelo Consentimento</button>
                                    <?php } ?>
                                    <?php if (!empty($vitrine->anexo_modelo_relatorio)) { ?>
                                        <button type="button" class="anexo-chip" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_modelo_relatorio) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Modelo Relatório</button>
                                    <?php } ?>
                                </div>
                                <div>
                                    <?php if (!empty($vitrine->anexo_resultado)) { ?>
                                        <button type="button" class="anexo-chip me-2" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_resultado) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Resultado</button>
                                    <?php } ?>
                                    <?php if (!empty($vitrine->anexo_resultado_recurso)) { ?>
                                        <button type="button" class="anexo-chip" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_resultado_recurso) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Resultado Recurso</button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </details>

        <?php foreach ($vitrinesPorAno as $ano => $itensAno) { ?>
            <details class="mt-3">
                <summary><strong><?= h($ano) ?></strong></summary>
                <div class="edital mt-3">
                    <?php foreach ($itensAno as $vitrine) { ?>
                        <div class="editalItem">
                            <div class="border">
                                <div class="editais box-simple">
                                    <?php if ($vitrineAbertaAgora($vitrine, $agoraTs)) { ?>
                                        <span class="vitrine-open-badge-solid" title="Edital aberto">Edital aberto</span>
                                    <?php } ?>
                                    <div class="box-simple-title h4"><?= $vitrine->nome ?></div>
                                    <?php if (!empty($vitrine->obs)) { ?>
                                        <div class="box-simple-time"><?= $vitrine->obs ?></div>
                                    <?php } ?>
                                </div>
                                <div class="p-2">
                                    <div class="mb-1">
                                        <?php if (!empty($vitrine->anexo_edital)) { ?>
                                            <button type="button" class="anexo-chip" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_edital) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Edital</button>
                                        <?php } ?>
                                        <?php if (!empty($vitrine->anexo_modelo_consentimento)) { ?>
                                            <button type="button" class="anexo-chip me-2" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_modelo_consentimento) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Modelo Consentimento</button>
                                        <?php } ?>
                                        <?php if (!empty($vitrine->anexo_modelo_relatorio)) { ?>
                                            <button type="button" class="anexo-chip" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_modelo_relatorio) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Modelo Relatório</button>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <?php if (!empty($vitrine->anexo_resultado)) { ?>
                                            <button type="button" class="anexo-chip me-2" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_resultado) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Resultado</button>
                                        <?php } ?>
                                        <?php if (!empty($vitrine->anexo_resultado_recurso)) { ?>
                                            <button type="button" class="anexo-chip" onclick="window.open('/uploads/editais/<?= h($vitrine->anexo_resultado_recurso) ?>', '_blank', 'noopener,noreferrer');"><span class="anexo-chip-icon" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none"><path d="M8 2v7m0 0 3-3m-3 3-3-3M3 11.5V13h10v-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Resultado Recurso</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </details>
        <?php } ?>
    </div>
</section>
