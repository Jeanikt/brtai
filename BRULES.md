```markdown
# BrotaAI - Regra de Negócio (Atualizada)

## Visão Geral
**BrotaAI** é uma plataforma SaaS para organização de eventos que utiliza um modelo **Freemium** com receita proveniente de **taxas de transação** e **assinaturas mensais**.

---

## Planos e Preços

### Trial de 30 Dias (Novos Usuários)
- **Duração:** 30 dias a partir do cadastro  
- **Mensalidade:** **R$ 0,00**  
- **Limite de convidados:** **Ilimitado**  
- **Taxa de transação:** **3,5% + R$0,85** por ingresso  
- **Eventos simultâneos:** **Ilimitados**  
- **Regras:**  
  - Ativado automaticamente no primeiro login  
  - Expira automaticamente após 30 dias  
  - **Volta ao Plano FREE** se não houver upgrade  

---

### Plano FREE
- **Mensalidade:** Grátis  
- **Limite de convidados:** 70 por evento  
- **Taxa de transação:** **6,5% + R$0,85** por ingresso  
- **Eventos simultâneos:** 1 ativo  

---

### Plano PRO
- **Mensalidade:** **R$29,00/mês** *(atualizado para compensar trial e taxas reduzidas)*  
- **Limite de convidados:** Ilimitado  
- **Taxa de transação:** **4,5% + R$0,85** por ingresso *(reduzida para fidelizar)*  
- **Eventos simultâneos:** Ilimitados  

---

## Cálculos de Exemplo  
**Cenário: Evento com 100 pessoas | Ingresso: R$12,50**

### Trial (30 dias – 100 pax)
```
Total Bruto: 100 × R$12,50 = R$1.250,00
Taxa/ingresso: (3,5% × R$12,50) + R$0,85 = R$1,2875

BROTA AI RECEBE: 100 × R$1,2875 = **R$128,75**
ORGANIZADOR RECEBE: R$1.250 - R$128,75 = **R$1.121,25**
```

### Plano FREE (após trial – máx. 70 pax)
```
Total Bruto: 70 × R$12,50 = R$875,00
Taxa/ingresso: (6,5% × R$12,50) + R$0,85 = R$1,6625

BROTA AI RECEBE: 70 × R$1,6625 = **R$116,38**
ORGANIZADOR RECEBE: R$875 - R$116,38 = **R$758,62**
```

### Plano PRO (100 pax)
```
Total Bruto: 100 × R$12,50 = R$1.250,00
Taxa/ingresso: (4,5% × R$12,50) + R$0,85 = R$1,4125

BROTA AI RECEBE:
   - Taxas: 100 × R$1,4125 = R$141,25
   - Mensalidade: R$29,00
   - TOTAL: **R$170,25**

ORGANIZADOR RECEBE: R$1.250 - R$141,25 - R$29 = **R$1.079,75**
```

---

## Comparação Direta (100 pessoas, R$12,50)

| Métrica               | **Trial (30d)** | **Free (70p)** | **Pro (100p)** | **Variação Pro vs Free** |
|-----------------------|-----------------|----------------|----------------|--------------------------|
| **Arrecadação Bruta** | R$1.250         | R$875          | R$1.250        | **+43%**                 |
| **Organizador Recebe**| **R$1.121,25**  | R$758,62       | **R$1.079,75** | **+R$321,13**            |
| **BrotaAI Recebe**    | R$128,75        | R$116,38       | R$170,25       | **+46%**                 |
| **Taxa Efetiva**      | 10,3%           | 13,3%          | 13,6%          | –                        |

---

## Estratégia de Monetização

### Princípios Fundamentais
1. **"Monetize onde seu cliente ganha dinheiro"**  
2. **Baixa barreira de entrada** → **Trial de 30 dias com Pro completo**  
3. **Upsell natural** pelo limite de 70 convidados + experiência do Pro  
4. **Alinhamento de interesses** – ambos ganham com mais vendas  

### Novo Argumento de Vendas
> **"Nos 30 dias de trial você ganha R$1.121 por evento.  
> No Free você cai para R$758.  
> No Pro, por apenas R$29/mês, você garante R$1.079 – **R$321 a mais que o Free!**  
> **Upgrade agora e nunca mais perca ingressos.**"

---

## Projeções Realistas (com Trial)

### Cenário Mês 6 (Base crescente + Trial)
```
200 novos cadastros/mês → 190 entram no trial
70% criam evento (133 eventos no trial)

Receita Trial: 133 × R$128,75 = R$17.123,75

50 organizadores FREE (pós-trial):  
50 × 50 ingressos × R$1,6625 × 2 eventos = R$8.312,50

60 organizadores PRO (conversão 45%):  
60 × 90 ingressos × R$1,4125 × 2 eventos = R$15.255,00  
60 × R$29 mensalidade = R$1.740,00  
TOTAL PRO: R$16.995,00

RECEITA TOTAL: R$17.123 + R$8.312 + R$16.995 = **R$42.430/mês**
```

### Projeção Anual
- **Receita Trial (taxas):** ~R$205.000/ano  
- **Receita Assinaturas Pro:** ~R$20.880/ano  
- **Taxas Free + Pro:** ~R$280.000/ano  
- **TOTAL ANUAL:** **~R$506.000/ano**

---

## Implementação Técnica

### Estrutura de Cobrança
```sql
-- Coluna no banco
ALTER TABLE users ADD COLUMN trial_ends_at DATETIME NULL;

-- Cálculo dinâmico
IF users.trial_ends_at > NOW() THEN
    porcentagem = 3.5; taxa_fixa = 0.85;
ELSEIF users.plan = 'pro' THEN
    porcentagem = 4.5; taxa_fixa = 0.85;
ELSE
    porcentagem = 6.5; taxa_fixa = 0.85;
END IF;
```

### Fluxo Financeiro
1. Cliente paga ingresso via Pix  
2. Valor fica em conta intermediária  
3. BrotaAI retém taxas automaticamente  
4. Saldo é liberado para organizador (1-2 dias úteis)

---

## Estratégia de Crescimento

### Fase 1 (MVP - 6 meses)
- **Trial de 30 dias como funil principal**  
- Conversão agressiva Free → Pro  
- Validação com eventos reais  

### Fase 2 (Escala - 6-12 meses)
- Introdução de **Plano Starter (R$9,90 | 150 pax | 5,0% + R$0,85)**  
- Redução gradual de taxas para clientes fiéis  
- Expansão regional  

### Fase 3 (Consolidação - 12+ meses)
- APIs para integração com fornecedores  
- Marketplace de serviços  
- Planos white-label  

---

## Considerações Importantes

### Para o Desenvolvimento
- **Trial automático** com `trial_ends_at`  
- Cálculo de taxa **dinâmico por usuário**  
- Dashboard com:  
  - Contador regressivo do trial  
  - Comparativo real de ganhos  
  - Botão **"Continuar no Pro"**

### Para o Marketing
- **Campanha “30 DIAS PRO GRÁTIS”** em todos os canais  
- E-mails automáticos (dia 0, 7, 15, 25, 30)  
- Depoimentos de quem **“não conseguiu voltar ao Free”**

---

## Resumo Executivo

**Vantagens do Novo Modelo:**
- **Trial de 30 dias com Pro completo + taxas reduzidas** → explosão de cadastros  
- **Taxa fixa R$0,85 mantida** → cobre processamento Pix  
- **Pro a R$29/mês com 4,5%** → mais acessível e lucrativo  
- **Conversão estimada: 45%** → receita recorrente explosiva  
- **Alinhamento perfeito:** organizador prova o crescimento, BrotaAI monetiza o sucesso  

**Meta Realista:** **R$40.000–50.000/mês** com 200–300 organizadores ativos em 6 meses

