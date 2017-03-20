
<table class="items" id="myTdable">
    <thead>
        <tr>
            <th rowspan="2">Mês</th>
            <th colspan="2">Investimentos</th>
            <th colspan="6">Operacionais</th>
        </tr>
        <tr>
            <th>Bens (USD)</th>
            <th>Equipamentos (USD)</th>
            <th>Taxas e Impostos (USD)</th>
            <th>Pessoal (USD)</th>
            <th>Transportes (USD)</th>
            <th>Serviços de Terceiros (USD)</th>
            <th>Combustivel e Lubrif. (USD)</th>
            <th>Outros Custos (USD)</th>

        </tr>
    </thead>
    <tfoot>
        <th>Totais</th>
        <th></th>
        <th></th>
        <th><?= number_format(($footer_royalty_total / 175),2,",",".") ?></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tfoot>

    <tbody>
        <?php for ($i = 0; $i < 12; $i++): ?>
            <tr>
                <td><?= $lista_meses[$i] ?></td>
                <td>-</td>
                <td>-</td>
                <td><?= number_format(($soma[$i] / 1.844 * (800/175) * 0.02),2,",",".") ?></td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
        <?php endfor ?>


        </tr>
    </tbody>
</table>
</div>
