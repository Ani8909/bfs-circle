<?php
require 'config.php';
try { $db->exec("ALTER TABLE bankers ADD COLUMN bank_name TEXT"); } catch (Exception $e) {}
try { $db->exec("ALTER TABLE bankers ADD COLUMN min_loan_limit REAL"); } catch (Exception $e) {}
try { $db->exec("ALTER TABLE bankers ADD COLUMN max_loan_limit REAL"); } catch (Exception $e) {}
try { $db->exec("ALTER TABLE bankers ADD COLUMN loan_category TEXT"); } catch (Exception $e) {}
echo "Altered successfully.";
