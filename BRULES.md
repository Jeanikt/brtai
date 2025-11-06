# BrotaAI - Regra de Negócio

## 📋 Visão Geral
**BrotaAI** é uma plataforma SaaS para organização de eventos que utiliza um modelo **Freemium** com receita proveniente de **taxas de transação** e **assinaturas mensais**.

---

## 🎯 Planos e Preços

### 🆓 Plano FREE
- **Mensalidade:** Grátis
- **Limite de convidados:** 70 por evento
- **Taxa de transação:** 6.5% + R$0,85 por ingresso
- **Eventos simultâneos:** 1 ativo

### 💎 Plano PRO
- **Mensalidade:** R$19,00/mês
- **Limite de convidados:** Ilimitado
- **Taxa de transação:** 5.5% + R$0,85 por ingresso
- **Eventos simultâneos:** Ilimitados

---

## 💰 Cálculos de Exemplo

### Cenário: Evento com 100 pessoas | Ingresso: R$30

#### 📊 PLANO FREE (máximo 70 convidados)
```
Total Bruto: 70 × R$30 = R$2.100,00
Taxa/ingresso: (6.5% × R$30) + R$0,85 = R$2,75

✅ BROTAAI RECEBE: 70 × R$2,75 = R$192,50
✅ ORGANIZADOR RECEBE: R$2.100 - R$192,50 = R$1.907,50

💡 O organizador PERDE a oportunidade de vender 30 ingressos extras!
```

#### 📈 PLANO PRO (100 convidados)
```
Total Bruto: 100 × R$30 = R$3.000,00
Taxa/ingresso: (5.5% × R$30) + R$0,85 = R$2,45

✅ BROTAAI RECEBE:
   - Taxas: 100 × R$2,45 = R$245,00
   - Mensalidade: R$19,00
   - TOTAL: R$264,00

✅ ORGANIZADOR RECEBE: R$3.000 - R$245 - R$19 = R$2.736,00
```

---

## 📊 Comparação Direta

| Métrica | Free (70 pax) | Pro (100 pax) | Variação |
|---------|---------------|---------------|----------|
| **Total Arrecadado** | R$2.100 | R$3.000 | **+43%** |
| **Organizador Recebe** | R$1.907 | R$2.736 | **+43%** |
| **BrotaAI Recebe** | R$192 | R$264 | **+37%** |
| **Taxa Efetiva** | 9,17% | 8,80% | **-0,37%** |

---

## 🎯 Estratégia de Monetização

### Princípios Fundamentais
1. **"Monetize onde seu cliente ganha dinheiro"**
2. **Baixa barreira de entrada** (Free atrai usuários)
3. **Upsell natural** pelo limite de 70 convidados
4. **Alinhamento de interesses** - ambos ganham com mais vendas

### Argumento de Vendas
> **"No Free você ganha R$1.907 por evento. No Pro você ganha R$2.736 - isso é R$829 A MAIS! Por apenas R$19/mês, você desbloqueia crescimento ilimitado."**

---

## 📈 Projeções Realistas

### Cenário Mês 6 (Base crescente)
```
50 organizadores FREE:
50 × 50 ingressos × R$2,75 × 2 eventos = R$13.750/mês

30 organizadores PRO:
30 × 90 ingressos × R$2,45 × 2 eventos = R$13.230/mês
30 × R$19 mensalidade = R$570/mês
TOTAL PRO: R$13.800/mês

🏆 RECEITA TOTAL: R$27.550/mês
```

### Projeção Anual (Cenário Realista)
- **Receita Assinaturas:** ~R$13.000/ano
- **Taxas Free:** ~R$17.000/ano  
- **Taxas Pro:** ~R$22.000/ano
- **TOTAL ANUAL:** **~R$52.000/ano**

---

## ⚙️ Implementação Técnica

### Estrutura de Cobrança
```sql
-- Exemplo de cálculo no banco de dados
taxa_transacao = (valor_ingresso * porcentagem_taxa) + taxa_fixa
receita_brotaai = (quantidade_ingressos * taxa_transacao) + mensalidade_pro
```

### Fluxo Financeiro
1. Cliente paga ingresso via Pix 
2. Valor fica em conta intermediária
3. BrotaAI retém taxas automaticamente
4. Saldo é liberado para organizador (1-2 dias úteis)

---

## 🚀 Estratégia de Crescimento

### Fase 1 (MVP - 6 meses)
- Foco em conversão Free → Pro
- Validação do modelo com eventos reais
- Ajuste fino das taxas baseado no feedback

### Fase 2 (Escala - 6-12 meses)  
- Introdução de plano Enterprise (R$99/mês)
- Redução gradual de taxas para clientes fiéis
- Expansão para novas regiões

### Fase 3 (Consolidação - 12+ meses)
- APIs para integração com fornecedores
- Marketplace de serviços para eventos
- Planos white-label para grandes clients

---

## ⚠️ Considerações Importantes

### Para o Desenvolvimento
- Sistema deve calcular automaticamente as taxas por plano
- Dashboard do organizador deve mostrar claramente:
  - Quanto já pagou em taxas
  - Quanto economizaria no plano Pro
  - Projeção de ganhos com upgrade

### Para o Marketing
- Comunicar transparentemente as taxas
- Destacar o "custo de oportunidade" do Free
- Mostrar casos reais de sucesso com números

---

## ✅ Resumo Executivo

**Vantagens deste Modelo:**
- ✅ **Para organizadores:** Pro é claramente melhor financeiramente
- ✅ **Para BrotaAI:** Maximiza receita com upsell natural
- ✅ **Limite estratégico:** 70 no Free cria dor que incentiva upgrade
- ✅ **Alinhamento perfeito:** Ambos ganham com mais vendas

**Meta Realista:** **R$25.000-30.000/mês** com 80-100 organizadores ativos

