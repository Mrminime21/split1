import mysql from 'mysql2/promise';
import dotenv from 'dotenv';

dotenv.config();

// Database connection pool
const pool = mysql.createPool({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'gainsmax_testtelegram',
  port: process.env.DB_PORT || 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Test database connection
const testConnection = async () => {
  try {
    const connection = await pool.getConnection();
    console.log('Database connection established successfully');
    connection.release();
    return true;
  } catch (error) {
    console.error('Database connection failed:', error.message);
    return false;
  }
};

// Execute query with parameters
const query = async (sql, params = []) => {
  try {
    const [results] = await pool.execute(sql, params);
    return results;
  } catch (error) {
    console.error('Database query error:', error.message);
    throw error;
  }
};

// Get a single row
const getOne = async (sql, params = []) => {
  const results = await query(sql, params);
  return results.length > 0 ? results[0] : null;
};

// Insert a record and return the ID
const insert = async (table, data) => {
  const keys = Object.keys(data);
  const values = Object.values(data);
  const placeholders = keys.map(() => '?').join(', ');
  
  const sql = `INSERT INTO ${table} (${keys.join(', ')}) VALUES (${placeholders})`;
  
  try {
    const result = await query(sql, values);
    return result.insertId;
  } catch (error) {
    console.error(`Error inserting into ${table}:`, error.message);
    throw error;
  }
};

// Update a record
const update = async (table, data, whereClause, whereParams = []) => {
  const keys = Object.keys(data);
  const values = Object.values(data);
  
  const setClause = keys.map(key => `${key} = ?`).join(', ');
  const sql = `UPDATE ${table} SET ${setClause} WHERE ${whereClause}`;
  
  try {
    const result = await query(sql, [...values, ...whereParams]);
    return result.affectedRows;
  } catch (error) {
    console.error(`Error updating ${table}:`, error.message);
    throw error;
  }
};

// Delete a record
const remove = async (table, whereClause, whereParams = []) => {
  const sql = `DELETE FROM ${table} WHERE ${whereClause}`;
  
  try {
    const result = await query(sql, whereParams);
    return result.affectedRows;
  } catch (error) {
    console.error(`Error deleting from ${table}:`, error.message);
    throw error;
  }
};

// Transaction support
const transaction = async (callback) => {
  const connection = await pool.getConnection();
  
  try {
    await connection.beginTransaction();
    const result = await callback(connection);
    await connection.commit();
    return result;
  } catch (error) {
    await connection.rollback();
    throw error;
  } finally {
    connection.release();
  }
};

export default {
  pool,
  testConnection,
  query,
  getOne,
  insert,
  update,
  remove,
  transaction
};