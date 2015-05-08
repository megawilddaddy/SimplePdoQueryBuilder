# SimplePdoQueryBuilder
Simple PDO Query Builder. Useful to generate low level queries with ease.

1) Installation

    "repositories": [
        {
            "url": "https://github.com/megawilddaddy/SimplePdoQueryBuilder.git",
            "type": "git"
        }
    ],
    "require": {
        "megawilddaddy/simple-pdo-query-builder": "dev-master"
    },

2) Usage

```sql

SELECT
  CONCAT(p.firstName, ' ', p.lastName) as partnerName, 
  m.fullName as managerName, 
  c.country, c.partnerId, 
  c.managerId, 
  CONCAT(c.firstName, ' ', c.lastName) as fullName, 
  a.group_name as groupName, 
  a.id as userId, 
  e.login AS login, 
  p.lastDepositDate, p.lastWithdrawalDate, 
  IFNULL(s.balance, 0) AS startingBalance, 
  IFNULL(e.balance, 0) AS endingBalance, 
  IFNULL(p.deposits - withdrawalRefunds, 0) AS deposits, 
  IFNULL(p.withdrawals + withdrawalRefunds - tc, 0) AS withdrawals, 
  IFNULL(p.withdrawalRefunds, 0) AS withdrawalRefunds, IFNULL(p.deposits + p.withdrawals - tc, 0) AS netDeposits,
  IFNULL(p.nbc, 0) AS nbc, IFNULL(p.pl, 0) AS pl, 
  a.agent_account as agentAccount, 
  IFNULL(swaps, 0) as swaps, IFNULL(e.bonusBalance, 0) as bonusBalance, 
  IFNULL(commission, 0) as commission, IFNULL(it, 0) as it, 
  IFNULL(vendorDeposits, 0) as vendorDeposits, 
  IFNULL(vendorWithdrawals + withdrawalRefunds, 0) as vendorWithdrawals, 
  IFNULL(clientProfit + tc, 0) as clientProfit, IFNULL(clientLoss, 0) as clientLoss, 
  IFNULL(bonus, 0) as bonus, 
  IFNULL(ibCommission, 0) as ibCommission, IFNULL(e.endFloatingPL, 0) as endFloatingPL, 
  IFNULL(p.credit, 0) AS credit, IFNULL(s.balance, 0) AS startingBalance, IFNULL(e.balance, 0) AS endingBalance, 
  IF(LEFT(a.GROUP_NAME, 1) = 'S', 'SV', 'CY') as broker, 
  IFNULL((-1 * (p.withdrawals + p.deposits - p.tc) - GREATEST(0, IFNULL(s.balance, 0)) + GREATEST(0, IFNULL(e.balance, 0)) + IFNULL(p.nbc, 0) + tc), 0) AS clientPL, 
  a.group_name AS group_name, 
  CASE WHEN SUBSTR(a.group_name, 2, 1) = 'E' THEN 'EUR' ELSE 'USD' END AS currency, IFNULL(a.equity, 0) AS equity, 
  IFNULL(p.pl / p.deposits * 100, 0) AS plFixed, 
  IF(a.group_name IN ('SUBS4P', 'SUBS5P'), (a.equity - a.credit)/a.credit*100, 
  (a.equity - a.credit - p.deposits - p.withdrawals + p.tc) / p.deposits * 100) AS equityPerformance, 
  ABS(ROUND((e.balance - s.balance - vendorDeposits - vendorWithdrawals - it - clientProfit - clientLoss - swaps - commission - nbc - ibCommission - withdrawalRefunds - tc), 2)) as checksum 
FROM
 
    (SELECT
        t.login, ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND close_time = '1970-01-01 00:00:00' THEN t.profit ELSE 0 END), 2) AS endFloatingPL, 
        ROUND(SUM(CASE WHEN t.cmd =7 THEN t.profit ELSE 0 END), 2) AS bonusBalance, 
        ROUND(SUM(CASE WHEN t.cmd = 6 AND close_time != '1970-01-01 00:00:00' THEN t.profit ELSE 0 END) + SUM(CASE WHEN t.cmd IN (0,1) AND close_time != '1970-01-01 00:00:00' THEN t.profit + t.swaps + t.commission ELSE 0 END), 2) AS balance 
    FROM
        test_trades t WHERE t.sid = 2 AND t.close_time <= '2015-05-09 00:00:00' 
    GROUP BY
        t.login) as e
     
LEFT JOIN
 
    (SELECT
        t.login, ROUND(SUM(CASE WHEN t.cmd = 6 THEN t.profit ELSE 0 END) + SUM(CASE WHEN t.cmd IN (0,1) AND close_time != '1970-01-01 00:00:00' THEN t.profit + t.swaps + t.commission ELSE 0 END), 2) AS balance 
    FROM
        test_trades t WHERE t.sid = 2 AND t.close_time BETWEEN '1980-01-01 00:00:00' AND '2015-05-07 00:00:00' 
    GROUP BY
        t.login ) as s ON s.login = e.login
         
LEFT JOIN
 
    (SELECT
        t.login, MAX(CASE WHEN t.cmd = 6 AND profit > 0 AND LEFT(comment, 2) = 'D-' THEN t.close_time ELSE null END) AS lastDepositDate, MAX(CASE WHEN t.cmd = 6 AND profit < 0 AND LEFT(comment, 2) = 'W-' THEN t.close_time ELSE null END) AS lastWithdrawalDate, ROUND(SUM(CASE WHEN t.cmd = 6 AND profit > 0 AND LEFT(comment, 2) = 'D-' THEN t.profit ELSE 0 END), 2) AS vendorDeposits, ROUND(SUM(CASE WHEN t.cmd = 6 AND profit < 0 AND LEFT(comment, 2) = 'W-' THEN t.profit ELSE 0 END), 2) AS vendorWithdrawals, ROUND(SUM(CASE WHEN t.cmd IN (6,7) AND LEFT(comment, 2) IN ('IT', 'PC', 'CP') THEN t.profit ELSE 0 END), 2) AS it, ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND profit > 0 THEN t.profit ELSE 0 END), 2) AS clientProfit, ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND profit < 0 THEN t.profit ELSE 0 END), 2) AS clientLoss, ROUND(SUM(CASE WHEN t.cmd = 6 AND profit > 0 THEN t.profit ELSE 0 END), 2) AS deposits, ROUND(SUM(CASE WHEN t.cmd = 6 AND profit < 0 THEN t.profit ELSE 0 END), 2) AS withdrawals, ROUND(SUM(CASE WHEN t.cmd = 6 AND LEFT(comment, 2) = 'TC' THEN t.profit ELSE 0 END), 2) AS tc, ROUND(SUM(CASE WHEN t.cmd = 6 AND profit > 0 AND RIGHT(COMMENT, 3) = 'DEC' THEN t.profit ELSE 0 END), 2) AS withdrawalRefunds, ROUND(SUM(CASE WHEN t.cmd = 7 THEN t.profit ELSE 0 END), 2) AS credit, ROUND(SUM(CASE WHEN t.comment IN ('DEPOSIT-NBC', 'ZERO-BALANCE') THEN t.profit ELSE 0 END), 2) AS nbc, ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND close_time != '1970-01-01 00:00:00' THEN t.profit + t.swaps + t.commission ELSE 0 END), 2) AS pl, ROUND(SUM(CASE WHEN t.close_time != '1970-01-01 00:00:00' THEN t.swaps ELSE 0 END), 2) AS swaps, ROUND(SUM(CASE WHEN t.close_time != '1970-01-01 00:00:00' THEN t.commission ELSE 0 END), 2) AS commission, ROUND(SUM(CASE WHEN t.cmd = 7 THEN t.profit ELSE 0 END), 2) AS bonus, ROUND(SUM(CASE WHEN LEFT(comment, 5) IN ('agent', 'COMMI') THEN t.profit ELSE 0 END), 2) as ibCommission 
    FROM
        test_trades t WHERE t.sid = 2 AND t.close_time BETWEEN '2015-05-07 00:00:00' AND '2015-05-09 00:00:00' 
    GROUP BY
        t.login) as p ON e.login = p.login
         
JOIN
     test_users a ON a.login = e.login AND a.sid = 2 
JOIN
     users c ON c.id = a.id 
LEFT JOIN
    managers m ON m.id = c.managerId 
LEFT JOIN
     users p ON p.id = c.partnerId 
HAVING
    fullName NOT LIKE '%test%' AND groupName NOT LIKE '%7P%' AND groupName NOT LIKE '%4P%' 
ORDER BY 
    login ASC

```

```php
$qb = new SimplePDOQueryBuilder();

$sQ = $qb->subQuery('s')
    ->select("t.login, ROUND(SUM(CASE WHEN t.cmd = 6 THEN t.profit ELSE 0 END) + SUM(CASE WHEN t.cmd IN (0,1) AND close_time != '1970-01-01 00:00:00' THEN t.profit + t.swaps + t.commission ELSE 0 END), 2) AS balance")
    ->from('test_trades t')
    ->where('t.sid = 2')
    ->where("t.close_time BETWEEN '1980-01-01 00:00:00' AND '{$from->format('Y-m-d H:i:00')}'")
    ->group('t.login')
;

$eQ = $qb->subQuery('e')
    ->select("t.login,
        ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND close_time = '1970-01-01 00:00:00' THEN t.profit ELSE 0 END), 2) AS endFloatingPL,
        ROUND(SUM(CASE WHEN t.cmd =7 THEN t.profit ELSE 0 END), 2) AS bonusBalance,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND close_time  != '1970-01-01 00:00:00' THEN t.profit ELSE 0 END) + SUM(CASE WHEN t.cmd IN (0,1) AND close_time != '1970-01-01 00:00:00' THEN t.profit + t.swaps + t.commission ELSE 0 END), 2) AS balance
")
    ->from('test_trades t')
    ->where('t.sid = 2')
    ->where(" t.close_time <= '{$to->format('Y-m-d H:i:00')}'")
    ->group('t.login')
    ;

$pQ = $qb->subQuery('p')
    ->select("
        t.login,
        MAX(CASE WHEN t.cmd = 6 AND profit > 0 AND LEFT(comment, 2) = 'D-' THEN t.close_time ELSE null END) AS lastDepositDate,
        MAX(CASE WHEN t.cmd = 6 AND profit < 0 AND LEFT(comment, 2) = 'W-' THEN t.close_time ELSE null END) AS lastWithdrawalDate,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND profit > 0 AND LEFT(comment, 2) = 'D-' THEN t.profit ELSE 0 END), 2) AS vendorDeposits,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND profit < 0 AND LEFT(comment, 2) = 'W-' THEN t.profit ELSE 0 END), 2) AS vendorWithdrawals,
        ROUND(SUM(CASE WHEN t.cmd IN (6,7) AND LEFT(comment, 2) IN ('IT', 'PC', 'CP') THEN t.profit ELSE 0 END), 2) AS it,
        ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND profit > 0 THEN t.profit ELSE 0 END), 2) AS clientProfit,
        ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND profit < 0 THEN t.profit ELSE 0 END), 2) AS clientLoss,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND profit > 0 THEN t.profit ELSE 0 END), 2) AS deposits,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND profit < 0 THEN t.profit ELSE 0 END), 2) AS withdrawals,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND LEFT(comment, 2) = 'TC' THEN t.profit ELSE 0 END), 2) AS tc,
        ROUND(SUM(CASE WHEN t.cmd = 6 AND profit > 0 AND RIGHT(COMMENT, 3) = 'DEC' THEN t.profit ELSE 0 END), 2) AS withdrawalRefunds,
        ROUND(SUM(CASE WHEN t.cmd = 7 THEN t.profit ELSE 0 END), 2) AS credit,
        ROUND(SUM(CASE WHEN t.comment IN ('DEPOSIT-NBC', 'ZERO-BALANCE') THEN t.profit ELSE 0 END), 2) AS nbc,
        ROUND(SUM(CASE WHEN t.cmd IN (0,1) AND close_time != '1970-01-01 00:00:00' THEN t.profit + t.swaps + t.commission ELSE 0 END), 2) AS pl,
        ROUND(SUM(CASE WHEN t.close_time != '1970-01-01 00:00:00' THEN t.swaps ELSE 0 END), 2) AS swaps,
        ROUND(SUM(CASE WHEN t.close_time != '1970-01-01 00:00:00' THEN t.commission ELSE 0 END), 2) AS commission,
        ROUND(SUM(CASE WHEN t.cmd = 7 THEN t.profit ELSE 0 END), 2) AS bonus,
        ROUND(SUM(CASE WHEN LEFT(comment, 5) IN ('agent', 'COMMI') THEN t.profit ELSE 0 END), 2) as ibCommission
    ")
    ->from('test_trades t')
    ->where('t.sid = 2')
    ->where("t.close_time BETWEEN '{$from->format('Y-m-d H:i:00')}' AND '{$to->format('Y-m-d H:i:00')}'")
    ->group('t.login');

$mQ = $qb->create();

if (!empty($filters['login'])) {
    $mQ->where($sQ->expr()->in('a.login', $filters['login']));
    $sQ->where($sQ->expr()->in('t.login', $filters['login']));
    $eQ->where($eQ->expr()->in('t.login', $filters['login']));
    $pQ->where($pQ->expr()->in('t.login', $filters['login']));
}
if (!empty($filters['book'])) {
    $mQ->where("SUBSTR(a.group_name, 3, 1) = '{$filters['book']}'");
}
if (!empty($filters['partnerId'])) {
    $mQ->where("c.partnerId = {$filters['partnerId']}");
}
if (!empty($filters['clientId'])) {
    $mQ->where("c.id = {$filters['clientId']}");
}
if (!empty($filters['company'])) {
    $mQ->where(" SUBSTR(a.group_name, 1, 1) = '{$filters['company']}' ");
}
if (!empty($filters['agentAccount'])) {
    $mQ->where("a.AGENT_ACCOUNT = '{$filters['agentAccount']}'");
}
if (!empty($filters['group'])) {
    $mQ->where($mQ->expr()->in('a.group_name', $filters['group']));
}
if (!empty($filters['sortBy'])) {
    $mQ->orderBy($filters['sortBy'], $filters['sortDir']);
}


$mQ->select("
       CONCAT(p.firstName, ' ', p.lastName) as partnerName,
       m.fullName as managerName,
       c.country,
       c.partnerId,
       c.managerId,
       CONCAT(c.firstName, ' ', c.lastName) as fullName,
       a.group_name as groupName,
       a.id as userId,
       e.login AS login,
       p.lastDepositDate,
       p.lastWithdrawalDate,
       IFNULL(s.balance, 0) AS startingBalance,
       IFNULL(e.balance, 0) AS endingBalance,
       IFNULL(p.deposits - withdrawalRefunds, 0)  AS deposits,
       IFNULL(p.withdrawals + withdrawalRefunds - tc, 0)  AS withdrawals,
       IFNULL(p.withdrawalRefunds, 0)  AS withdrawalRefunds,
       IFNULL(p.deposits + p.withdrawals - tc, 0) AS netDeposits,
       IFNULL(p.nbc, 0) AS nbc,
       IFNULL(p.pl, 0) AS pl,
       a.agent_account as agentAccount,
       IFNULL(swaps, 0) as swaps,
       IFNULL(e.bonusBalance, 0) as bonusBalance,
       IFNULL(commission, 0) as commission,
       IFNULL(it, 0) as it,
       IFNULL(vendorDeposits, 0) as vendorDeposits,
       IFNULL(vendorWithdrawals + withdrawalRefunds, 0) as vendorWithdrawals,
       IFNULL(clientProfit + tc, 0) as clientProfit,
       IFNULL(clientLoss, 0) as clientLoss,
       IFNULL(bonus, 0) as bonus,
       IFNULL(ibCommission, 0) as ibCommission,
       IFNULL(e.endFloatingPL, 0) as endFloatingPL,
       IFNULL(p.credit, 0) AS credit,
       IFNULL(s.balance, 0) AS startingBalance,
       IFNULL(e.balance, 0) AS endingBalance,
       IF(LEFT(a.GROUP_NAME, 1) = 'S', 'SV', 'CY') as broker,
       IFNULL((-1 * (p.withdrawals + p.deposits - p.tc) - GREATEST(0, IFNULL(s.balance, 0)) + GREATEST(0, IFNULL(e.balance, 0)) + IFNULL(p.nbc, 0) + tc), 0) AS clientPL,
       a.group_name AS group_name,
       CASE WHEN SUBSTR(a.group_name, 2, 1) = 'E' THEN 'EUR' ELSE 'USD' END AS currency,
       IFNULL(a.equity, 0) AS equity,
       IFNULL(p.pl / p.deposits * 100, 0)  AS plFixed,
       IF(a.group_name IN ('SUBS4P', 'SUBS5P'), (a.equity - a.credit)/a.credit*100, (a.equity - a.credit - p.deposits - p.withdrawals + p.tc) / p.deposits * 100) AS equityPerformance,
       ABS(ROUND((e.balance - s.balance - vendorDeposits - vendorWithdrawals - it - clientProfit - clientLoss - swaps - commission - nbc - ibCommission - withdrawalRefunds - tc), 2)) as checksum
    ")
    ->from($eQ)
    ->leftJoin($sQ, 's.login = e.login')
    ->leftJoin($pQ, 'e.login = p.login')
    ->join('test_users a', 'a.login = e.login AND a.sid = 2')
    ->join('users c', 'c.id = a.id')
    ->leftJoin('managers m', 'm.id = c.managerId')
    ->leftJoin('users p', 'p.id = c.partnerId')
    ->having("
        fullName NOT LIKE '%test%'
        AND (startingBalance != 0 OR vendorDeposits != 0 OR  vendorWithdrawals != 0 OR  it != 0 OR  nbc != 0 OR  clientLoss != 0 OR  clientProfit != 0 OR  swaps != 0 OR  commission != 0 OR  ibCommission != 0 OR  endingBalance != 0)
        AND groupName NOT LIKE '%7P%'
        AND groupName NOT LIKE '%4P%'
    ");

$stmt = $this->connection->prepare($mQ->getSql());
$stmt->execute();

$result = $stmt->fetchAll();
```